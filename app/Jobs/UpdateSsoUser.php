<?php

namespace App\Jobs;

use App\Data\Authentik\User as AuthentikUser;
use App\Models\Group;
use App\Models\User;
use App\Services\AuthentikService;
use App\Services\AuthentikServiceImpl;
use DateTimeInterface;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class UpdateSsoUser implements ShouldBeUniqueUntilProcessing, ShouldQueue
{
    use Queueable;

    public bool $deleteWhenMissingModels = true;

    public int $uniqueFor = 900;

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
        $user = User::find($this->user->id);

        if (! $user) {
            return;
        }

        $this->user = $user;

        $previousSsoId = is_scalar($user->sso_id) ? trim((string) $user->sso_id) : '';

        if ($previousSsoId === '') {
            CreateSsoUser::dispatch($user);

            return;
        }

        $payload = AuthentikUser::fromModel($user);
        $payload->path = $user->is_member ? $this->memberPath : $this->nonMemberPath;
        $payload->type = $user->is_member ? $this->memberType : $this->nonMemberType;

        try {
            $ssoUser = $authentikService->updateUser($previousSsoId, $payload);
        } catch (RequestException $exception) {
            if ($exception->response?->status() !== 404) {
                throw $exception;
            }

            $trimmedEmail = trim((string) $user->email_address);
            $targetEmail = strtolower($trimmedEmail);
            $ssoUsers = $targetEmail !== ''
                ? $authentikService->listUsers(['email' => $trimmedEmail])
                : collect();

            $existingUser = $ssoUsers->first(
                fn (AuthentikUser $candidate) => is_string($candidate->email_address)
                    && strtolower(trim($candidate->email_address)) === $targetEmail
            );

            $existingSsoId = $existingUser !== null && is_scalar($existingUser->sso_id) && ! is_bool($existingUser->sso_id)
                ? trim((string) $existingUser->sso_id)
                : '';

            if ($existingSsoId !== '') {
                $ssoUser = $authentikService->updateUser($existingSsoId, $payload);
            } else {
                $ssoUser = $authentikService->createUser($payload);
            }
        }

        $newSsoId = $ssoUser->requireSsoId();
        $user->sso_id = $newSsoId;
        $user->sso_last_synced_at = $syncedAt;

        User::withoutTimestamps(fn () => $user->saveQuietly());

        if ($previousSsoId !== $newSsoId && $newSsoId !== '') {
            $groupIds = $user->groups()->pluck('groups.id')->all();

            if ($groupIds !== []) {
                Group::whereIn('id', $groupIds)->update(['updated_at' => now()]);
            }
        }
    }
}
