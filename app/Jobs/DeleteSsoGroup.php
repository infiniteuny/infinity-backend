<?php

namespace App\Jobs;

use App\Services\SsoService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class DeleteSsoGroup implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $groupSsoId,
    ) {}

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 5;

    /**
     * Get the middleware the job should pass through.
     *
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [
            new WithoutOverlapping($this->groupSsoId),
        ];
    }

    /**
     * Execute the job.
     */
    public function handle(SsoService $ssoService): void
    {
        $ssoService->deleteGroup($this->groupSsoId);
    }
}
