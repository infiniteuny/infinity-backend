<?php

namespace App\Jobs;

use App\Data\Authentik\Group as AuthentikGroup;
use App\Models\Group;
use App\Services\AuthentikService;
use App\Services\AuthentikServiceImpl;
use DateTimeInterface;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class CreateSsoGroup implements ShouldBeUniqueUntilProcessing, ShouldQueue
{
    use Queueable;

    public bool $deleteWhenMissingModels = true;

    public int $uniqueFor = 900;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Group $group,
        /** @var string[] */
        public ?array $ssoParentIds = null,
        public ?bool $isSuperuser = null,
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
            (new WithoutOverlapping("sso-group:{$this->group->id}"))
                ->shared()
                ->releaseAfter(10)
                ->expireAfter(240),
        ];
    }

    /**
     * Execute the job.
     */
    public function handle(AuthentikService $authentikService): void
    {
        if ($authentikService instanceof AuthentikServiceImpl && ! $authentikService->isConfigured()) {
            return;
        }

        $syncedAt = now();
        $group = Group::find($this->group->id);

        if (! $group) {
            return;
        }

        $this->group = $group;

        $payload = AuthentikGroup::fromModel($group);

        if (! is_null($this->ssoParentIds)) {
            $parentIds = array_values(array_filter(
                $this->ssoParentIds,
                fn ($id) => is_string($id) && trim($id) !== '',
            ));

            if ($parentIds !== []) {
                $payload->sso_parent_ids = $parentIds;
            }
        }

        if (! is_null($this->isSuperuser)) {
            $payload->is_superuser = $this->isSuperuser;
        }

        $ssoGroup = null;
        $existingSsoId = is_string($group->sso_id) ? trim($group->sso_id) : '';

        if ($existingSsoId !== '') {
            try {
                $ssoGroup = $authentikService->updateGroup($existingSsoId, $payload);
            } catch (RequestException $exception) {
                if ($exception->response?->status() !== 404) {
                    throw $exception;
                }
            }
        }

        if (is_null($ssoGroup)) {
            $ssoGroups = $authentikService->listGroups([
                'name' => $group->name,
            ]);

            $existingGroup = $ssoGroups->first(
                fn (AuthentikGroup $candidate) => $candidate->name === $group->name
            );

            $matchedSsoId = $existingGroup !== null && is_scalar($existingGroup->sso_id) && ! is_bool($existingGroup->sso_id)
                ? trim((string) $existingGroup->sso_id)
                : '';

            if ($matchedSsoId !== '') {
                $ssoGroup = $authentikService->updateGroup($matchedSsoId, $payload);
            } else {
                $ssoGroup = $authentikService->createGroup($payload);
            }
        }

        $group->sso_id = $ssoGroup->requireSsoId();
        $group->sso_last_synced_at = $syncedAt;

        Group::withoutTimestamps(fn () => $group->saveQuietly());
    }
}
