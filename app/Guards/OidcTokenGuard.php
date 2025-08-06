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

        // Check if the token subject exists in the users database
        $user = $this->provider->retrieveByCredentials(['sso_id' => $tokenPayload['sub']]);

        // If the user is not found, try to get the user info from the OIDC server
        if (is_null($user)) {
            try {
                $userInfo = $this->oidcService->getUserInfo($credentials['token']);

                /** @var User $user */
                $user = $this->provider->retrieveByCredentials(['email_address' => $userInfo['email']]);

                if (! is_null($user)) {
                    $user->sso_id = $tokenPayload['sub'];
                    $user->save();
                } else {
                    return false;
                }
            } catch (Throwable $e) {
                return false;
            }
        }

        // Check if the token is already used to authenticate before
        $token = Token::withTrashed()->where('sso_id', $tokenPayload['uid'])->first();

        // If the token is not found in the database, check if it is still active
        if (is_null($token)) {
            try {
                $tokenInfo = $this->oidcService->introspect($credentials['token']);

                if ($tokenInfo['active']) {
                    $token = new Token([
                        'sso_id' => $tokenPayload['uid'],
                        'user_id' => $user->id,
                        'created_at' => Carbon::createFromTimestamp($tokenInfo['iat']),
                        'expires_at' => Carbon::createFromTimestamp($tokenInfo['exp']),
                    ]);
                } else {
                    return false;
                }
            } catch (Throwable $e) {
                return false;
            }
        } else {
            if ($token->trashed()) {
                return false;
            }
        }

        $token->last_used_at = Carbon::now();
        $token->save();
        $this->setUser($user);

        return true;
    }

    /**
     * Get the access token from the current request
     */
    private function getAccessToken(): ?string
    {
        return $this->request->bearerToken();
    }
}
