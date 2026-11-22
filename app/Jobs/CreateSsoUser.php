<?php

namespace App\Jobs;

use App\Data\Authentik\User as AuthentikUser;
use App\Models\User;
use App\Services\AuthentikService;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
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
            new WithoutOverlapping($this->user->id),
        ];
    }

    /**
     * Execute the job.
     */
    public function handle(AuthentikService $authentikService): void
    {
        $ssoUsers = $authentikService->listUsers([
            'email' => $this->user->email_address,
        ]);

        if ($ssoUsers->isNotEmpty()) {
            $ssoUser = $ssoUsers->first();
            $ssoUser->path = $this->user->is_member ? $this->memberPath : $this->nonMemberPath;
            $ssoUser->type = $this->user->is_member ? $this->memberType : $this->nonMemberType;
            $ssoUser = $authentikService->updateUser($ssoUser->sso_id, $ssoUser);
        } else {
            $ssoUser = AuthentikUser::fromModel($this->user)->additional([
                'path' => $this->user->is_member ? $this->memberPath : $this->nonMemberPath,
                'type' => $this->user->is_member ? $this->memberType : $this->nonMemberType,
            ]);
            $ssoUser = $authentikService->createUser($ssoUser);
        }

        $this->user->sso_id = (string) $ssoUser->sso_id;
        $this->user->sso_last_synced_at = now();
        $this->user->save();
    }
}
