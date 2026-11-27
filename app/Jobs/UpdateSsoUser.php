<?php

namespace App\Jobs;

use App\Data\Authentik\User as AuthentikUser;
use App\Models\User;
use App\Services\AuthentikService;
use DateTimeInterface;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class UpdateSsoUser implements ShouldBeUniqueUntilProcessing, ShouldQueue
{
    use Queueable;

    public string $memberPath = 'infiniteuny.id';

    public string $memberType = 'internal';

    public string $nonMemberPath = 'uny.ac.id';

    public string $nonMemberType = 'external';

    /**
     * Create a new job instance.
     */
    public function __construct(
        public User $user,
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
        return $this->user->id;
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping("sso-user:{$this->user->id}"))
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
        $ssoUser = AuthentikUser::fromModel($this->user)->additional([
            'path' => $this->user->is_member ? $this->memberPath : $this->nonMemberPath,
            'type' => $this->user->is_member ? $this->memberType : $this->nonMemberType,
        ]);
        $ssoUser = $authentikService->updateUser($this->user->sso_id, $ssoUser);

        $this->user->sso_id = (string) $ssoUser->sso_id;
        $this->user->sso_last_synced_at = now();
        $this->user->save();
    }
}
