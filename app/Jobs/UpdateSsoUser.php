<?php

namespace App\Jobs;

use App\Data\SsoUserData;
use App\Models\User;
use App\Services\SsoService;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class UpdateSsoUser implements ShouldBeUniqueUntilProcessing, ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public User $user,
        public string $memberPath = 'infiniteuny.id',
        public string $memberType = 'internal',
        public string $nonMemberPath = 'uny.ac.id',
        public string $nonMemberType = 'external',
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
    public function handle(SsoService $ssoService): void
    {
        $ssoUser = SsoUserData::fromModel($this->user)->additional([
            'path' => $this->user->is_member ? $this->memberPath : $this->nonMemberPath,
            'type' => $this->user->is_member ? $this->memberType : $this->nonMemberType,
        ]);
        $ssoUser = $ssoService->updateUser($this->user->sso_id, $ssoUser);

        $this->user->update([
            'sso_id' => $ssoUser->sso_id,
            'sso_last_synced_at' => now(),
        ]);
    }
}
