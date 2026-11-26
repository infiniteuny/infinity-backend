<?php

namespace App\Jobs;

use App\Services\AuthentikService;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class DeleteSsoGroup implements ShouldBeUniqueUntilProcessing, ShouldQueue
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
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return $this->groupSsoId;
    }

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
    public function handle(AuthentikService $authentikService): void
    {
        try {
            $authentikService->deleteGroup($this->groupSsoId);
        } catch (RequestException $exception) {
            if ($exception->response?->status() !== 404) {
                throw $exception;
            }
        }
    }
}
