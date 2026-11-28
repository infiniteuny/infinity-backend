<?php

namespace App\Jobs;

use App\Data\Outline\Group as OutlineGroup;
use App\Data\Outline\Invite;
use App\Data\Outline\User as OutlineUser;
use App\Models\CoreTeam;
use App\Models\Group;
use App\Models\GroupIntegration;
use App\Models\User;
use App\Services\OutlineService;
use App\Services\OutlineServiceImpl;
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

    public bool $deleteWhenMissingModels = true;

    public int $uniqueFor = 900;

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
        public bool $syncCollectionAfter = false,
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
                ->expireAfter(240),
        ];
    }

    /**
     * Execute the job.
     */
    public function handle(OutlineService $outlineService): void
    {
        if ($outlineService instanceof OutlineServiceImpl && ! $outlineService->isConfigured()) {
            return;
        }

        $syncedAt = now();
        $group = Group::find($this->group->id);

        if (! $group) {
            return;
        }

        $this->group = $group;

        $integration = GroupIntegration::where('group_id', $group->id)->first();
        $docsGroupId = is_string($integration?->docs_group_id) ? trim($integration->docs_group_id) : '';

        if ($docsGroupId === '') {
            CreateDocsGroup::dispatch(
                $group,
                $this->docsParentIds,
                $this->docsRemoveParentIds,
                $this->suppressInviteEmail,
                $this->syncCollectionAfter,
            );

            return;
        }

        $groupRecreated = false;

        try {
            $outlineService->updateGroup($docsGroupId, new OutlineGroup(
                id: Optional::create(),
                name: $group->name,
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

            $recreatedGroup = $this->findOrCreateGroup($outlineService, $group->name);
            $docsGroupId = $this->requireRemoteId($recreatedGroup->id, 'group');
            $groupRecreated = true;
        }

        $this->syncGroupUsers($outlineService, $group, $docsGroupId);

        $needsCreateCollection = false;
        $needsUpdateCollection = false;

        DB::transaction(function () use ($group, $docsGroupId, $groupRecreated, $syncedAt, &$needsCreateCollection, &$needsUpdateCollection) {
            if (! Group::whereKey($group->id)->exists()) {
                return;
            }

            $fresh = GroupIntegration::where('group_id', $group->id)
                ->lockForUpdate()
                ->first() ?? new GroupIntegration(['group_id' => $group->id]);

            if ($groupRecreated || ($fresh->exists && $fresh->docs_group_id !== $docsGroupId)) {
                $fresh->docs_collection_last_synced_at = null;
            }

            $fresh->docs_group_id = $docsGroupId;
            $fresh->docs_group_last_synced_at = $syncedAt;
            $fresh->save();

            $needsCreateCollection = is_null($fresh->docs_collection_id);
            $needsUpdateCollection = ! $needsCreateCollection && is_null($fresh->docs_collection_last_synced_at);
        });

        if ($this->syncCollectionAfter && ($needsCreateCollection || $needsUpdateCollection)) {
            $coreTeam = CoreTeam::where('group_id', $group->id)->first();

            if ($coreTeam) {
                $collectionName = 'Core Team '.$coreTeam->year;
                $readOnlyGroupIds = array_values(array_filter(array_map(
                    fn ($id) => is_string($id) ? trim($id) : '',
                    [
                        config('services.outline.core_team_group_id'),
                        config('services.outline.xcore_team_group_id'),
                    ],
                )));

                if ($needsCreateCollection) {
                    CreateDocsCollection::dispatch($group, $collectionName, $readOnlyGroupIds);
                } else {
                    UpdateDocsCollection::dispatch($group, $collectionName, $readOnlyGroupIds);
                }
            }
        }
    }

    private function findOrCreateGroup(OutlineService $outlineService, string $name): OutlineGroup
    {
        $offset = 0;

        do {
            $groups = $outlineService->listGroups([
                'query' => $name,
                'limit' => self::PAGE_LIMIT,
                'offset' => $offset,
            ]);

            $existing = $groups->first(
                fn (OutlineGroup $group) => is_string($group->name) && $group->name === $name
            );

            if ($existing !== null) {
                $this->requireRemoteId($existing->id, 'group');

                return $existing;
            }

            $offset += self::PAGE_LIMIT;
        } while ($groups->count() === self::PAGE_LIMIT);

        $created = $outlineService->createGroup(new OutlineGroup(
            id: Optional::create(),
            name: $name,
            description: Optional::create(),
            icon: Optional::create(),
            color: Optional::create(),
            externalId: Optional::create(),
            memberCount: Optional::create(),
        ));

        $this->requireRemoteId($created->id, 'group');

        return $created;
    }

    private function syncGroupUsers(OutlineService $outlineService, Group $group, string $docsGroupId): void
    {
        $group->loadMissing(['users.coreTeams']);

        $usersWithEmail = $group->users
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

        $parentIdsToAdd = array_values(array_unique(array_filter(array_map(
            fn ($id) => is_string($id) ? trim($id) : '',
            $this->docsParentIds ?? [],
        ))));
        $parentIdsToRemove = array_values(array_unique(array_filter(array_map(
            fn ($id) => is_string($id) ? trim($id) : '',
            $this->docsRemoveParentIds ?? [],
        ))));
        $sharedGroupIds = array_values(array_unique(array_merge($parentIdsToAdd, $parentIdsToRemove)));

        $existingCohortMembers = $this->fetchGroupMembers($outlineService, $docsGroupId);
        $initialCohortMembers = $existingCohortMembers;

        /** @var array<string, array<string, true>> $existingSharedMemberIds */
        $existingSharedMemberIds = [];
        foreach ($sharedGroupIds as $sharedGroupId) {
            $sharedExists = true;
            $members = $this->fetchGroupMembers($outlineService, $sharedGroupId, $sharedExists);
            if ($sharedExists) {
                $existingSharedMemberIds[$sharedGroupId] = array_fill_keys(array_keys($members), true);
            }
        }

        $desiredOutlineUserIds = [];

        foreach ($usersWithEmail as $user) {
            $outlineUser = $outlineUsersByEmail[strtolower(trim($user->email_address))];
            $outlineUserId = $this->requireRemoteId($outlineUser->id, 'user');
            $desiredOutlineUserIds[$outlineUserId] = true;

            if (! isset($existingCohortMembers[$outlineUserId])) {
                $outlineService->addUserToGroup($docsGroupId, $outlineUserId);
                $existingCohortMembers[$outlineUserId] = $outlineUser;
            }

            foreach ($parentIdsToAdd as $parentId) {
                if (
                    array_key_exists($parentId, $existingSharedMemberIds)
                    && $this->shouldUserBelongToSharedGroup($user, $parentId, default: true)
                    && ! isset($existingSharedMemberIds[$parentId][$outlineUserId])
                ) {
                    $outlineService->addUserToGroup($parentId, $outlineUserId);
                    $existingSharedMemberIds[$parentId][$outlineUserId] = true;
                }
            }

            foreach ($parentIdsToRemove as $removeParentId) {
                if (
                    array_key_exists($removeParentId, $existingSharedMemberIds)
                    && ! $this->shouldUserBelongToSharedGroup($user, $removeParentId, default: false)
                    && isset($existingSharedMemberIds[$removeParentId][$outlineUserId])
                ) {
                    $this->removeUserFromGroupSafely($outlineService, $removeParentId, $outlineUserId);
                    unset($existingSharedMemberIds[$removeParentId][$outlineUserId]);
                }
            }
        }

        /** @var array<string, OutlineUser> $staleMembers */
        $staleMembers = [];
        foreach ($initialCohortMembers as $memberId => $member) {
            if (! isset($desiredOutlineUserIds[$memberId])) {
                $staleMembers[$memberId] = $member;
            }
        }

        foreach ($staleMembers as $staleMemberId => $staleMember) {
            $this->removeUserFromGroupSafely($outlineService, $docsGroupId, $staleMemberId);
        }

        if ($staleMembers !== [] && $existingSharedMemberIds !== []) {
            $this->reconcileRemovedMembersSharedGroups(
                $outlineService,
                $staleMembers,
                $existingSharedMemberIds,
            );
        }
    }

    /**
     * @return array<string, OutlineUser>
     */
    private function fetchGroupMembers(
        OutlineService $outlineService,
        string $outlineGroupId,
        bool &$groupExists = true,
    ): array {
        $members = [];
        $offset = 0;

        try {
            do {
                $batch = $outlineService->listGroupMembers($outlineGroupId, [
                    'limit' => self::PAGE_LIMIT,
                    'offset' => $offset,
                ]);

                foreach ($batch as $member) {
                    if (is_string($member->id) && trim($member->id) !== '') {
                        $members[trim($member->id)] = $member;
                    }
                }

                $offset += self::PAGE_LIMIT;
            } while ($batch->count() === self::PAGE_LIMIT);
        } catch (RequestException $exception) {
            if ($exception->response?->status() !== 404) {
                throw $exception;
            }

            $groupExists = false;
        }

        return $members;
    }

    private function removeUserFromGroupSafely(OutlineService $outlineService, string $groupId, string $userId): void
    {
        try {
            $outlineService->removeUserFromGroup($groupId, $userId);
        } catch (RequestException $exception) {
            if ($exception->response?->status() !== 404) {
                throw $exception;
            }
        }
    }

    private function shouldUserBelongToSharedGroup(?User $user, string $outlineGroupId, bool $default): bool
    {
        if ($user === null) {
            return false;
        }

        $coreTeamGroupId = is_string($v = config('services.outline.core_team_group_id')) ? trim($v) : '';
        $xcoreTeamGroupId = is_string($v = config('services.outline.xcore_team_group_id')) ? trim($v) : '';

        if ($coreTeamGroupId !== '' && $outlineGroupId === $coreTeamGroupId) {
            return $user->coreTeams->contains(fn (CoreTeam $team) => (bool) $team->is_active);
        }

        if ($xcoreTeamGroupId !== '' && $outlineGroupId === $xcoreTeamGroupId) {
            $hasActive = $user->coreTeams->contains(fn (CoreTeam $team) => (bool) $team->is_active);
            $hasInactive = $user->coreTeams->contains(fn (CoreTeam $team) => ! $team->is_active);

            return ! $hasActive && $hasInactive;
        }

        return $default;
    }

    /**
     * @param  array<string, OutlineUser>  $staleMembers
     * @param  array<string, array<string, true>>  $existingSharedMemberIds
     */
    private function reconcileRemovedMembersSharedGroups(
        OutlineService $outlineService,
        array $staleMembers,
        array &$existingSharedMemberIds,
    ): void {
        /** @var array<string, ?string> $staleUserEmails */
        $staleUserEmails = [];

        foreach ($staleMembers as $outlineUserId => $member) {
            $email = is_string($member->email) && trim($member->email) !== ''
                ? strtolower(trim($member->email))
                : null;

            if ($email === null) {
                try {
                    $outlineUser = $outlineService->getUser($outlineUserId);
                    $email = is_string($outlineUser->email) && trim($outlineUser->email) !== ''
                        ? strtolower(trim($outlineUser->email))
                        : null;
                } catch (RequestException $exception) {
                    if ($exception->response?->status() === 404) {
                        continue;
                    }

                    throw $exception;
                }
            }

            $staleUserEmails[$outlineUserId] = $email;
        }

        $localUsersByEmail = $this->loadLocalUsersByEmail(
            array_values(array_unique(array_filter($staleUserEmails)))
        );

        foreach ($staleUserEmails as $outlineUserId => $normalizedEmail) {
            $localUser = $normalizedEmail !== null ? ($localUsersByEmail[$normalizedEmail] ?? null) : null;

            foreach ($existingSharedMemberIds as $sharedGroupId => &$memberSet) {
                $shouldBelong = $this->shouldUserBelongToSharedGroup($localUser, $sharedGroupId, default: false);
                $currentlyInGroup = isset($memberSet[$outlineUserId]);

                if ($shouldBelong && ! $currentlyInGroup) {
                    $outlineService->addUserToGroup($sharedGroupId, $outlineUserId);
                    $memberSet[$outlineUserId] = true;
                } elseif (! $shouldBelong && $currentlyInGroup) {
                    $this->removeUserFromGroupSafely($outlineService, $sharedGroupId, $outlineUserId);
                    unset($memberSet[$outlineUserId]);
                }
            }
            unset($memberSet);
        }
    }

    /**
     * @param  string[]  $normalizedEmails
     * @return array<string, User>
     */
    private function loadLocalUsersByEmail(array $normalizedEmails): array
    {
        if ($normalizedEmails === []) {
            return [];
        }

        $localUsersByEmail = [];

        foreach (array_chunk($normalizedEmails, self::PAGE_LIMIT) as $emailChunk) {
            $placeholders = implode(', ', array_fill(0, count($emailChunk), '?'));
            $users = User::with(['coreTeams'])
                ->whereRaw("LOWER(TRIM(email_address)) IN ({$placeholders})", $emailChunk)
                ->get();

            foreach ($users as $user) {
                if (is_string($user->email_address) && trim($user->email_address) !== '') {
                    $localUsersByEmail[strtolower(trim($user->email_address))] = $user;
                }
            }
        }

        return $localUsersByEmail;
    }

    private function requireRemoteId(mixed $id, string $resource): string
    {
        if ($id instanceof Optional || ! is_scalar($id) || is_bool($id)) {
            throw new RuntimeException("Outline {$resource} response is missing a valid scalar id.");
        }

        $normalized = trim((string) $id);

        if ($normalized === '') {
            throw new RuntimeException("Outline {$resource} response returned an empty id.");
        }

        return $normalized;
    }
}
