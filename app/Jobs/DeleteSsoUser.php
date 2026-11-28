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

class DeleteSsoUser implements ShouldBeUniqueUntilProcessing, ShouldQueue
{
    use Queueable;

    public int $uniqueFor = 900;

    public string $ssoId;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $userSsoId = '',
        public ?string $userId = null,
        ?string $ssoId = null,
    ) {
        $resolved = $ssoId ?? $userSsoId;
        $this->userSsoId = $resolved;
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
        $userId = is_string($this->userId) ? trim($this->userId) : '';

        return $userId !== '' ? "user:{$userId}" : $this->ssoId;
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array<int, object>
     */
    public function middleware(): array
    {
        $userId = is_string($this->userId) ? trim($this->userId) : '';
        $lockKey = $userId !== ''
            ? "sso-user:{$userId}"
            : "sso-user-delete:{$this->ssoId}";

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

        $targetSsoId = trim($this->ssoId !== '' ? $this->ssoId : $this->userSsoId);

        if ($targetSsoId === '') {
            return;
        }

        try {
            $authentikService->deleteUser($targetSsoId);
        } catch (RequestException $exception) {
            if ($exception->response?->status() !== 404) {
                throw $exception;
            }
        }
    }
}
