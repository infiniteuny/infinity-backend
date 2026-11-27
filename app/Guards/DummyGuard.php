<?php

namespace App\Guards;

use App\Models\User;
use Faker\Factory as FakerFactory;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DummyGuard implements Guard
{
    use GuardHelpers;

    protected Request $request;

    protected ?string $dummyUserId;

    /**
     * Create a new authentication guard.
     *
     * @return void
     */
    public function __construct(
        UserProvider $provider,
        Request $request,
        ?string $dummyUserId
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
        $user = null;

        if (is_string($this->dummyUserId) && Str::isUuid($this->dummyUserId)) {
            $user = User::where('id', $this->dummyUserId)->first();
        }

        if (! $user) {
            if (class_exists(FakerFactory::class)) {
                $user = User::factory()->make();
            } else {
                $user = new User([
                    'name' => 'Dummy User',
                    'username' => 'dummyuser',
                    'email_address' => 'dummy@infiniteuny.id',
                    'phone_number' => '+6280000000000',
                    'student_id' => '00000000000',
                    'major_id' => (string) Str::uuid7(),
                    'links' => (object) [],
                    'is_member' => true,
                    'is_extraordinary' => false,
                ]);
                $user->id = (string) Str::uuid7();
            }
        }

        $this->setUser($user);

        return true;
    }

    /**
     * Set the current request instance.
     */
    public function setRequest(Request $request): static
    {
        $this->request = $request;

        return $this;
    }
}
