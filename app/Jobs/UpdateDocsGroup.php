<?php

namespace App\Jobs;

use App\Data\Outline\Group as OutlineGroup;
use App\Data\Outline\Invite;
use App\Models\CommunityGroupAdmin;
use App\Models\CoreTeam;
use App\Models\Group;
use App\Models\GroupIntegration;
use App\Models\User;
use App\Services\OutlineService;
use DateTimeInterface;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Spatie\LaravelData\Optional;

class UpdateDocsGroup implements ShouldBeUniqueUntilProcessing, ShouldQueue
{
    use Queueable;

    private const PAGE_LIMIT = 100;

    private const INVITE_BATCH_SIZE = 20;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Group $group,
        /** @var string[]|null */
        public ?array $docsParentIds = null,
        /** @var string[]|null */
        public ?array $docsRemoveParentIds = null,
        public bool $suppressInviteEmail = false,
    ) {}

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     */
    public int $maxExceptions = 5;

    /**
     * The number of seconds to wait before retrying an exception.
     */
    public int $backoff = 10;

    /**
     * Determine the time at which the job should timeout.
     */
    public function retryUntil(): DateTimeInterface
    {
        return now()->addMinutes(15);
    }

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return $this->group->id;
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping("docs-group:{$this->group->id}"))
                ->shared()
                ->releaseAfter(15)
                ->expireAfter(120),
        ];
    }

    /**
     * Execute the job.
     */
    public function handle(OutlineService $outlineService): void
    {
        $integration = GroupIntegration::where('group_id', $this->group->id)->first();

        if (is_null($integration?->docs_group_id)) {
            CreateDocsGroup::dispatch(
                $this->group,
                $this->docsParentIds,
                $this->docsRemoveParentIds,
                $this->suppressInviteEmail,
            );

            return;
        }

        $docsGroupId = $integration->docs_group_id;
        $groupRecreated = false;

        try {
            $outlineService->updateGroup($docsGroupId, new OutlineGroup(
                id: Optional::create(),
                name: $this->group->name,
                description: Optional::create(),
                icon: Optional::create(),
                color: Optional::create(),
                externalId: Optional::create(),
                memberCount: Optional::create(),
            ));
        } catch (RequestException $exception) {
            if ($exception->response?->status() !== 404) {
                throw $exception;
            }

            $recreatedGroup = $this->findOrCreateGroup($outlineService, $this->group->name);
            $docsGroupId = $recreatedGroup->id;
            $groupRecreated = true;
        }

        $this->syncGroupUsers($outlineService, $docsGroupId);

        DB::transaction(function () use ($docsGroupId, $groupRecreated) {
            $fresh = GroupIntegration::where('group_id', $this->group->id)
                ->lockForUpdate()
                ->first() ?? new GroupIntegration(['group_id' => $this->group->id]);

            if ($groupRecreated || ($fresh->exists && $fresh->docs_group_id !== $docsGroupId)) {
                $fresh->docs_collection_last_synced_at = null;
            }

            $fresh->docs_group_id = $docsGroupId;
            $fresh->docs_group_last_synced_at = now();
            $fresh->save();
        });
    }

    private function findOrCreateGroup(OutlineService $outlineService, string $name): OutlineGroup
    {
        $existing = $outlineService->listGroups([
            'name' => $name,
            'limit' => self::PAGE_LIMIT,
        ])->first(fn (OutlineGroup $group) => is_string($group->name) && $group->name === $name);

        if ($existing !== null) {
            return $existing;
        }

        return $outlineService->createGroup(new OutlineGroup(
            id: Optional::create(),
            name: $name,
            description: Optional::create(),
            icon: Optional::create(),
            color: Optional::create(),
            externalId: Optional::create(),
            memberCount: Optional::create(),
        ));
    }

    private function syncGroupUsers(OutlineService $outlineService, string $docsGroupId): void
    {
        $this->group->loadMissing(['users.coreTeams', 'users.communityGroupAdmins']);

        $usersWithEmail = $this->group->users
            ->filter(
                fn (User $user) => is_string($user->email_address)
                    && filter_var(trim($user->email_address), FILTER_VALIDATE_EMAIL) !== false
            )
            ->values();

        $outlineUsersByEmail = [];

        foreach ($usersWithEmail->map(fn (User $u) => strtolower(trim($u->email_address)))->unique()->chunk(self::PAGE_LIMIT) as $emailChunk) {
            $foundUsers = $outlineService->listUsers([
                'emails' => $emailChunk->values()->all(),
                'filter' => 'all',
                'limit' => self::PAGE_LIMIT,
            ]);

            foreach ($foundUsers as $foundUser) {
                if (is_string($foundUser->email)) {
                    $outlineUsersByEmail[strtolower(trim($foundUser->email))] = $foundUser;
                }
            }
        }

        $missingInvites = [];
        $missingUsers = [];

        foreach ($usersWithEmail as $user) {
            $emailKey = strtolower(trim($user->email_address));

            if (! isset($outlineUsersByEmail[$emailKey])) {
                $missingInvites[$emailKey] = new Invite(
                    name: $user->name,
                    email: $emailKey,
                );
                $missingUsers[] = $user;
            }
        }

        foreach (array_chunk(array_values($missingInvites), self::INVITE_BATCH_SIZE) as $inviteChunk) {
            $invitedUsers = $outlineService->inviteUsers($inviteChunk, $this->suppressInviteEmail);

            foreach ($invitedUsers as $invitedUser) {
                if (is_string($invitedUser->email)) {
                    $outlineUsersByEmail[strtolower(trim($invitedUser->email))] = $invitedUser;
                }
            }
        }

        foreach ($missingUsers as $missingUser) {
            $emailKey = strtolower(trim($missingUser->email_address));

            if (! isset($outlineUsersByEmail[$emailKey])) {
                $fallback = $outlineService->listUsers([
                    'emails' => [$emailKey],
                    'filter' => 'all',
                    'limit' => 1,
                ])->first();

                if ($fallback === null) {
                    throw new RuntimeException("Unable to resolve Outline user for email [{$missingUser->email_address}].");
                }

                $outlineUsersByEmail[$emailKey] = $fallback;
            }
        }

        $parentIdsToAdd = array_values(array_filter($this->docsParentIds ?? []));
        $parentIdsToRemove = array_values(array_filter($this->docsRemoveParentIds ?? []));
        $desiredOutlineUserIds = [];

        foreach ($usersWithEmail as $user) {
            $outlineUser = $outlineUsersByEmail[strtolower(trim($user->email_address))];
            $desiredOutlineUserIds[$outlineUser->id] = true;

            $outlineService->addUserToGroup($docsGroupId, $outlineUser->id);

            foreach ($parentIdsToAdd as $parentId) {
                if ($this->shouldUserBelongToSharedGroup($user, $parentId, default: true)) {
                    $outlineService->addUserToGroup($parentId, $outlineUser->id);
                }
            }

            foreach ($parentIdsToRemove as $removeParentId) {
                if (! $this->shouldUserBelongToSharedGroup($user, $removeParentId, default: false)) {
                    $outlineService->removeUserFromGroup($removeParentId, $outlineUser->id);
                }
            }
        }

        $sharedGroupIds = array_values(array_unique(array_merge($parentIdsToAdd, $parentIdsToRemove)));
        $staleMemberIds = [];
        $offset = 0;

        do {
            $batch = $outlineService->listGroupMembers($docsGroupId, [
                'limit' => self::PAGE_LIMIT,
                'offset' => $offset,
            ]);

            foreach ($batch as $currentMember) {
                if (
                    is_string($currentMember->id)
                    && $currentMember->id !== ''
                    && ! isset($desiredOutlineUserIds[$currentMember->id])
                ) {
                    $staleMemberIds[$currentMember->id] = true;
                }
            }

            $offset += self::PAGE_LIMIT;
        } while ($batch->count() === self::PAGE_LIMIT);

        foreach (array_keys($staleMemberIds) as $staleMemberId) {
            try {
                $outlineService->removeUserFromGroup($docsGroupId, $staleMemberId);
            } catch (RequestException $exception) {
                if ($exception->response?->status() !== 404) {
                    throw $exception;
                }
            }

            $this->reconcileRemovedMemberSharedGroups($outlineService, $staleMemberId, $sharedGroupIds);
        }
    }

    private function shouldUserBelongToSharedGroup(?User $user, string $outlineGroupId, bool $default): bool
    {
        if ($user === null) {
            return false;
        }

        $coreTeamGroupId = config('services.outline.core_team_group_id');
        $xcoreTeamGroupId = config('services.outline.xcore_team_group_id');
        $cgAdminGroupId = config('services.outline.cg_admin_group_id');
        $xcgAdminGroupId = config('services.outline.xcg_admin_group_id');

        if ($outlineGroupId === $coreTeamGroupId) {
            return $user->coreTeams->contains(fn (CoreTeam $team) => (bool) $team->is_active);
        }

        if ($outlineGroupId === $xcoreTeamGroupId) {
            $hasActive = $user->coreTeams->contains(fn (CoreTeam $team) => (bool) $team->is_active);
            $hasInactive = $user->coreTeams->contains(fn (CoreTeam $team) => ! $team->is_active);

            return ! $hasActive && $hasInactive;
        }

        if (! empty($cgAdminGroupId) && $outlineGroupId === $cgAdminGroupId) {
            return $user->communityGroupAdmins->contains(fn (CommunityGroupAdmin $admin) => (bool) $admin->is_active);
        }

        if (! empty($xcgAdminGroupId) && $outlineGroupId === $xcgAdminGroupId) {
            $hasActive = $user->communityGroupAdmins->contains(fn (CommunityGroupAdmin $admin) => (bool) $admin->is_active);
            $hasInactive = $user->communityGroupAdmins->contains(fn (CommunityGroupAdmin $admin) => ! $admin->is_active);

            return ! $hasActive && $hasInactive;
        }

        return $default;
    }

    /**
     * @param  string[]  $sharedGroupIds
     */
    private function reconcileRemovedMemberSharedGroups(
        OutlineService $outlineService,
        string $outlineUserId,
        array $sharedGroupIds,
    ): void {
        if (empty($sharedGroupIds)) {
            return;
        }

        try {
            $outlineUser = $outlineService->getUser($outlineUserId);
        } catch (RequestException $exception) {
            if ($exception->response?->status() === 404) {
                return;
            }

            throw $exception;
        }

        if (! is_string($outlineUser->email) || $outlineUser->email === '') {
            return;
        }

        $localUser = User::with(['coreTeams', 'communityGroupAdmins'])
            ->whereRaw('LOWER(email_address) = ?', [strtolower($outlineUser->email)])
            ->first();

        foreach ($sharedGroupIds as $sharedGroupId) {
            if ($this->shouldUserBelongToSharedGroup($localUser, $sharedGroupId, default: false)) {
                $outlineService->addUserToGroup($sharedGroupId, $outlineUserId);
            } else {
                $outlineService->removeUserFromGroup($sharedGroupId, $outlineUserId);
            }
        }
    }
}
