<?php

namespace App\Jobs;

use App\Data\SsoGroupData;
use App\Models\Group;
use App\Services\SsoService;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Spatie\LaravelData\Optional;

class CreateSsoGroup implements ShouldBeUniqueUntilProcessing, ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Group $group,
        public ?string $ssoParentId = null,
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
    public function handle(SsoService $ssoService): void
    {
        $ssoGroups = $ssoService->listGroups([
            'name' => $this->group->name,
        ]);

        if ($ssoGroups->isNotEmpty()) {
            $ssoGroup = $ssoGroups->first();
        } else {
            $ssoGroup = SsoGroupData::fromModel($this->group)->additional([
                'sso_parent_id' => $this->ssoParentId ?? Optional::create(),
                'is_superuser' => $this->isSuperuser ?? false,
            ]);
            $ssoGroup = $ssoService->createGroup($ssoGroup);
        }

        $this->group->update([
            'sso_id' => $ssoGroup->sso_id,
            'sso_last_synced_at' => now(),
        ]);
    }
}
