<?php

namespace App\Jobs;

use App\Data\Authentik\User as AuthentikUser;
use App\Models\Group;
use App\Models\User;
use App\Services\AuthentikService;
use DateTimeInterface;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class CreateSsoUser implements ShouldBeUniqueUntilProcessing, ShouldQueue
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
        $payload = AuthentikUser::fromModel($this->user);
        $payload->path = $this->user->is_member ? $this->memberPath : $this->nonMemberPath;
        $payload->type = $this->user->is_member ? $this->memberType : $this->nonMemberType;

        $previousSsoId = is_scalar($this->user->sso_id) ? trim((string) $this->user->sso_id) : '';
        $ssoUser = null;

        if ($previousSsoId !== '') {
            try {
                $ssoUser = $authentikService->updateUser($previousSsoId, $payload);
            } catch (RequestException $exception) {
                if ($exception->response?->status() !== 404) {
                    throw $exception;
                }
            }
        }

        if (is_null($ssoUser)) {
            $targetEmail = strtolower(trim((string) $this->user->email_address));
            $ssoUsers = $targetEmail !== ''
                ? $authentikService->listUsers(['email' => $this->user->email_address])
                : collect();

            $existingUser = $ssoUsers->first(
                fn (AuthentikUser $candidate) => is_string($candidate->email_address)
                    && strtolower(trim($candidate->email_address)) === $targetEmail
            );

            if ($existingUser !== null && ! is_null($existingUser->sso_id) && (string) $existingUser->sso_id !== '') {
                $ssoUser = $authentikService->updateUser((string) $existingUser->sso_id, $payload);
            } else {
                $ssoUser = $authentikService->createUser($payload);
            }
        }

        $newSsoId = (string) $ssoUser->sso_id;
        $this->user->sso_id = $newSsoId;
        $this->user->sso_last_synced_at = now();

        User::withoutTimestamps(fn () => $this->user->saveQuietly());

        if ($previousSsoId !== $newSsoId && $newSsoId !== '') {
            $groupIds = $this->user->groups()->pluck('groups.id')->all();

            if ($groupIds !== []) {
                Group::whereIn('id', $groupIds)->update(['updated_at' => now()]);
            }
        }
    }
}
