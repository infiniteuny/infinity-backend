<?php

namespace App\Guards;

use App\Models\User;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;

class DummyGuard implements Guard
{
    use GuardHelpers;

    protected Request $request;

    protected string $dummyUserId;

    /**
     * Create a new authentication guard.
     *
     * @return void
     */
    public function __construct(
        UserProvider $provider,
        Request $request,
        string $dummyUserId
    ) {
        $this->request = $request;
        $this->provider = $provider;
        $this->dummyUserId = $dummyUserId;
        $this->user = null;
    }

    /**
     * Get the currently authenticated user.
     */
    public function user(): ?Authenticatable
    {
        if (! is_null($this->user)) {
            return $this->user;
        }

        if ($this->validate()) {
            return $this->user;
        }

        return $this->user;
    }

    /**
     * Validate a user's credentials.
     */
    public function validate(array $credentials = []): bool
    {
        /** @var Authenticatable $user */
        $user = User::where('id', $this->dummyUserId)
            ->first();

        if (! $user) {
            $user = User::factory()->make();
        }

        $this->setUser($user);

        return true;
    }
}
