<?php

namespace App\Guards;

use App\Models\Token;
use App\Models\User;
use App\Services\OidcService;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Throwable;

class OidcTokenGuard implements Guard
{
    use GuardHelpers;

    protected Request $request;

    protected OidcService $oidcService;

    protected ?Request $lastAttemptedRequest = null;

    /**
     * Create a new authentication guard.
     *
     * @return void
     */
    public function __construct(
        UserProvider $provider,
        OidcService $oidcService,
        Request $request
    ) {
        $this->request = $request;
        $this->provider = $provider;
        $this->oidcService = $oidcService;
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

        if ($this->lastAttemptedRequest === $this->request) {
            return null;
        }

        $this->lastAttemptedRequest = $this->request;

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
        if (empty($credentials['token'])) {
            if (! $credentials['token'] = $this->getAccessToken()) {
                return false;
            }
        }

        // Verify the token signature
        try {
            $tokenPayload = $this->oidcService->verify($credentials['token']);
        } catch (Throwable $e) {
            return false;
        }

        if (empty($tokenPayload['sub']) || empty($tokenPayload['uid'])) {
            return false;
        }

        $ssoId = (string) $tokenPayload['sub'];
        $tokenUid = (string) $tokenPayload['uid'];
        $shouldBindSsoId = false;

        // Check if the token subject exists in the users database
        $user = $this->provider->retrieveByCredentials(['sso_id' => $ssoId]);

        // If the user is not found, try to get the user info from the OIDC server
        if (is_null($user)) {
            try {
                $userInfo = $this->oidcService->getUserInfo($credentials['token']);
                $email = isset($userInfo['email'])
                    ? mb_strtolower(trim((string) $userInfo['email']), 'UTF-8')
                    : '';

                if ($email === '') {
                    return false;
                }

                /** @var User|null $user */
                $user = User::whereRaw('LOWER(TRIM(email_address)) = ?', [$email])->first();

                if (is_null($user)) {
                    return false;
                }

                $shouldBindSsoId = true;
            } catch (Throwable $e) {
                return false;
            }
        }

        // Check if the token is already used to authenticate before
        $token = Token::withTrashed()->where('sso_id', $tokenUid)->first();

        // If the token is not found in the database, check if it is still active
        if (is_null($token)) {
            try {
                $tokenInfo = $this->oidcService->introspect($credentials['token']);

                if (! empty($tokenInfo['active']) && isset($tokenInfo['iat'], $tokenInfo['exp'])) {
                    $token = Token::withTrashed()->createOrFirst(
                        ['sso_id' => $tokenUid],
                        [
                            'user_id' => $user->id,
                            'last_used_at' => Carbon::now(),
                            'created_at' => Carbon::createFromTimestamp($tokenInfo['iat']),
                            'expires_at' => Carbon::createFromTimestamp($tokenInfo['exp']),
                        ],
                    );
                } else {
                    return false;
                }
            } catch (Throwable $e) {
                return false;
            }
        }

        if ($token->trashed() || $token->user_id !== $user->id) {
            return false;
        }

        if ($shouldBindSsoId) {
            User::withoutTimestamps(function () use ($user, $ssoId) {
                $user->sso_id = $ssoId;
                $user->saveQuietly();
            });
        }

        if (! $token->wasRecentlyCreated) {
            $token->last_used_at = Carbon::now();
            $token->save();
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

    /**
     * Get the access token from the current request
     */
    private function getAccessToken(): ?string
    {
        return $this->request->bearerToken();
    }
}
