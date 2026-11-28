<?php

namespace App\Jobs;

use App\Services\AuthentikService;
use App\Services\AuthentikServiceImpl;
use DateTimeInterface;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class DeleteSsoGroup implements ShouldBeUniqueUntilProcessing, ShouldQueue
{
    use Queueable;

    public int $uniqueFor = 900;

    public string $ssoId;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $groupSsoId = '',
        public ?string $groupId = null,
        ?string $ssoId = null,
    ) {
        $resolved = $ssoId ?? $groupSsoId;
        $this->groupSsoId = $resolved;
        $this->ssoId = $resolved;
    }

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
        $groupId = is_string($this->groupId) ? trim($this->groupId) : '';

        return $groupId !== '' ? "group:{$groupId}" : $this->ssoId;
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array<int, object>
     */
    public function middleware(): array
    {
        $groupId = is_string($this->groupId) ? trim($this->groupId) : '';
        $lockKey = $groupId !== ''
            ? "sso-group:{$groupId}"
            : "sso-group-delete:{$this->ssoId}";

        return [
            (new WithoutOverlapping($lockKey))
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

        $targetSsoId = trim($this->ssoId !== '' ? $this->ssoId : $this->groupSsoId);

        if ($targetSsoId === '') {
            return;
        }

        try {
            $authentikService->deleteGroup($targetSsoId);
        } catch (RequestException $exception) {
            if ($exception->response?->status() !== 404) {
                throw $exception;
            }
        }
    }
}
