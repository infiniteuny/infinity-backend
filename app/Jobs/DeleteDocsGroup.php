<?php

namespace App\Jobs;

use App\Data\Outline\User as OutlineUser;
use App\Models\CommunityGroupAdmin;
use App\Models\CoreTeam;
use App\Models\User;
use App\Services\OutlineService;
use DateTimeInterface;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class DeleteDocsGroup implements ShouldBeUniqueUntilProcessing, ShouldQueue
{
    use Queueable;

    private const PAGE_LIMIT = 100;

    /**
     * Create a new job instance.
     *
     * @param  string[]|null  $sharedGroupIds
     * @param  string[]  $fallbackMemberEmails
     */
    public function __construct(
        public string $docsGroupId,
        public ?string $groupId = null,
        public ?array $sharedGroupIds = null,
        public array $fallbackMemberEmails = [],
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
        return $this->docsGroupId;
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array<int, object>
     */
    public function middleware(): array
    {
        $lockKey = $this->groupId
            ? "docs-group:{$this->groupId}"
            : "docs-group-delete:{$this->docsGroupId}";

        return [
            (new WithoutOverlapping($lockKey))
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
        $sharedGroupIds = array_values(array_unique(array_filter($this->sharedGroupIds ?? [])));
        $groupExists = true;

        if ($sharedGroupIds !== []) {
            $groupMembers = $this->fetchGroupMembers($outlineService, $this->docsGroupId, $groupExists);
            $this->reconcileSharedGroups($outlineService, $groupMembers, $sharedGroupIds);
        }

        if ($groupExists) {
            try {
                $outlineService->deleteGroup($this->docsGroupId);
            } catch (RequestException $exception) {
                if ($exception->response?->status() !== 404) {
                    throw $exception;
                }
            }
        }
    }

    /**
     * @return array<string, OutlineUser>
     */
    private function fetchGroupMembers(
        OutlineService $outlineService,
        string $outlineGroupId,
        bool &$groupExists,
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
                    if (is_string($member->id) && $member->id !== '') {
                        $members[$member->id] = $member;
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

    /**
     * @param  array<string, OutlineUser>  $groupMembers
     * @param  string[]  $sharedGroupIds
     */
    private function reconcileSharedGroups(
        OutlineService $outlineService,
        array $groupMembers,
        array $sharedGroupIds,
    ): void {
        /** @var array<string, ?string> $outlineUsersToReconcile */
        $outlineUsersToReconcile = [];
        $resolvedEmails = [];

        foreach ($groupMembers as $outlineUserId => $member) {
            $email = is_string($member->email) && trim($member->email) !== ''
                ? strtolower(trim($member->email))
                : null;

            if ($email === null) {
                try {
                    $fullUser = $outlineService->getUser($outlineUserId);
                    $email = is_string($fullUser->email) && trim($fullUser->email) !== ''
                        ? strtolower(trim($fullUser->email))
                        : null;
                } catch (RequestException $exception) {
                    if ($exception->response?->status() === 404) {
                        continue;
                    }

                    throw $exception;
                }
            }

            $outlineUsersToReconcile[$outlineUserId] = $email;

            if ($email !== null) {
                $resolvedEmails[$email] = true;
            }
        }

        $remainingFallbackEmails = collect($this->fallbackMemberEmails)
            ->map(fn ($email) => is_string($email) ? strtolower(trim($email)) : '')
            ->filter(fn (string $email) => $email !== '' && ! isset($resolvedEmails[$email]))
            ->unique()
            ->values();

        foreach ($remainingFallbackEmails->chunk(self::PAGE_LIMIT) as $emailChunk) {
            $foundUsers = $outlineService->listUsers([
                'emails' => $emailChunk->values()->all(),
                'filter' => 'all',
                'limit' => self::PAGE_LIMIT,
            ]);

            foreach ($foundUsers as $foundUser) {
                if (
                    ! is_string($foundUser->id)
                    || $foundUser->id === ''
                    || array_key_exists($foundUser->id, $outlineUsersToReconcile)
                ) {
                    continue;
                }

                $outlineUsersToReconcile[$foundUser->id] = is_string($foundUser->email) && trim($foundUser->email) !== ''
                    ? strtolower(trim($foundUser->email))
                    : null;
            }
        }

        if ($outlineUsersToReconcile === []) {
            return;
        }

        $localUsersByEmail = $this->loadLocalUsersByEmail(
            array_values(array_unique(array_filter($outlineUsersToReconcile)))
        );

        /** @var array<string, array<string, true>> $currentSharedMembers */
        $currentSharedMembers = [];
        foreach ($sharedGroupIds as $sharedGroupId) {
            $sharedExists = true;
            $members = $this->fetchGroupMembers($outlineService, $sharedGroupId, $sharedExists);

            if ($sharedExists) {
                $currentSharedMembers[$sharedGroupId] = array_fill_keys(array_keys($members), true);
            }
        }

        foreach ($outlineUsersToReconcile as $outlineUserId => $email) {
            $localUser = $email !== null ? ($localUsersByEmail[$email] ?? null) : null;

            foreach ($currentSharedMembers as $sharedGroupId => $memberSet) {
                $shouldBelong = $this->shouldUserBelongToSharedGroup($localUser, $sharedGroupId);
                $currentlyInSharedGroup = isset($memberSet[$outlineUserId]);

                if ($shouldBelong && ! $currentlyInSharedGroup) {
                    $outlineService->addUserToGroup($sharedGroupId, $outlineUserId);
                } elseif (! $shouldBelong && $currentlyInSharedGroup) {
                    try {
                        $outlineService->removeUserFromGroup($sharedGroupId, $outlineUserId);
                    } catch (RequestException $exception) {
                        if ($exception->response?->status() !== 404) {
                            throw $exception;
                        }
                    }
                }
            }
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
            $users = User::with([
                'coreTeams:id,is_active',
                'communityGroupAdmins:id,is_active',
            ])
                ->select(['users.id', 'users.email_address'])
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

    private function shouldUserBelongToSharedGroup(?User $user, string $outlineGroupId): bool
    {
        if ($user === null) {
            return false;
        }

        $coreTeamGroupId = config('services.outline.core_team_group_id');
        $xcoreTeamGroupId = config('services.outline.xcore_team_group_id');
        $cgAdminGroupId = config('services.outline.cg_admin_group_id');
        $xcgAdminGroupId = config('services.outline.xcg_admin_group_id');

        if (! empty($coreTeamGroupId) && $outlineGroupId === $coreTeamGroupId) {
            return $user->coreTeams->contains(fn (CoreTeam $team) => (bool) $team->is_active);
        }

        if (! empty($xcoreTeamGroupId) && $outlineGroupId === $xcoreTeamGroupId) {
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

        return false;
    }
}
