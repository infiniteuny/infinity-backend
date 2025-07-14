<?php

namespace App\Jobs;

use App\Data\SsoUserData;
use App\Models\User;
use App\Services\SsoService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class CreateSsoUser implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public User $user,
        public bool $isActive = true,
        public string $path = 'infiniteuny.id',
        public string $type = 'internal',
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
            new WithoutOverlapping($this->user->id),
        ];
    }

    /**
     * Execute the job.
     */
    public function handle(SsoService $ssoService): void
    {
        $ssoUsers = $ssoService->listUsers([
            'email' => $this->user->email_address,
        ]);

        if ($ssoUsers->isNotEmpty()) {
            $ssoUser = $ssoUsers->first();
        } else {
            $ssoUser = SsoUserData::fromModel($this->user)->additional([
                'is_active' => $this->isActive,
                'path' => $this->path,
                'type' => $this->type,
            ]);
            $ssoUser = $ssoService->createUser($ssoUser);
        }

        $this->user->update([
            'sso_id' => $ssoUser->sso_id,
            'sso_last_synced_at' => now(),
        ]);
    }
}
