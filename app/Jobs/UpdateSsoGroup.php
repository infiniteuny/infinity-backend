<?php

namespace App\Jobs;

use App\Data\Authentik\Group as AuthentikGroup;
use App\Models\Group;
use App\Services\AuthentikService;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class UpdateSsoGroup implements ShouldBeUniqueUntilProcessing, ShouldQueue
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
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 5;

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
            new WithoutOverlapping($this->group->id),
        ];
    }

    /**
     * Execute the job.
     */
    public function handle(AuthentikService $authentikService): void
    {
        if (is_null($this->group->sso_id)) {
            CreateSsoGroup::dispatch(
                $this->group,
                $this->ssoParentIds,
                $this->isSuperuser,
            );

            return;
        }

        $ssoGroup = AuthentikGroup::fromModel($this->group);

        if (! is_null($this->ssoParentIds)) {
            $ssoGroup->sso_parent_ids = array_values(array_filter($this->ssoParentIds));
        }

        if (! is_null($this->isSuperuser)) {
            $ssoGroup->is_superuser = $this->isSuperuser;
        }

        $ssoGroup = $authentikService->updateGroup($this->group->sso_id, $ssoGroup);

        $this->group->sso_id = $ssoGroup->sso_id;
        $this->group->sso_last_synced_at = now();
        $this->group->save();
    }
}
