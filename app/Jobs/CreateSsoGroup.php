<?php

namespace App\Jobs;

use App\Data\Authentik\Group as AuthentikGroup;
use App\Models\Group;
use App\Services\AuthentikService;
use DateTimeInterface;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class CreateSsoGroup implements ShouldBeUniqueUntilProcessing, ShouldQueue
{
    use Queueable;

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
                ->expireAfter(120),
        ];
    }

    /**
     * Execute the job.
     */
    public function handle(AuthentikService $authentikService): void
    {
        $payload = AuthentikGroup::fromModel($this->group);

        if (! is_null($this->ssoParentIds)) {
            $payload->sso_parent_ids = array_values(array_filter($this->ssoParentIds));
        }

        if (! is_null($this->isSuperuser)) {
            $payload->is_superuser = $this->isSuperuser;
        }

        $ssoGroup = null;

        if ($this->group->sso_id) {
            try {
                $ssoGroup = $authentikService->updateGroup($this->group->sso_id, $payload);
            } catch (RequestException $exception) {
                if ($exception->response?->status() !== 404) {
                    throw $exception;
                }
            }
        }

        if (is_null($ssoGroup)) {
            $ssoGroups = $authentikService->listGroups([
                'name' => $this->group->name,
            ]);

            if ($ssoGroups->isNotEmpty()) {
                $existingGroup = $ssoGroups->first(
                    fn (AuthentikGroup $group) => $group->name === $this->group->name
                ) ?? $ssoGroups->first();

                $ssoGroup = $authentikService->updateGroup($existingGroup->sso_id, $payload);
            } else {
                $ssoGroup = $authentikService->createGroup($payload);
            }
        }

        $this->group->sso_id = $ssoGroup->sso_id;
        $this->group->sso_last_synced_at = now();
        $this->group->save();
    }
}
