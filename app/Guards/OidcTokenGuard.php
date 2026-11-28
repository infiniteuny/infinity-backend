<?php

namespace App\Guards;

use App\Models\Group;
use App\Models\Token;
use App\Models\User;
use App\Services\OidcService;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Database\UniqueConstraintViolationException;
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

        // If the user is not found, try to match an unbound user from OIDC userinfo
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
                $user = User::whereRaw('LOWER(TRIM(email_address)) = ?', [$email])
                    ->where(static function ($query): void {
                        $query->whereNull('sso_id')->orWhere('sso_id', '');
                    })
                    ->first();

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
        $now = Carbon::now();

        // If the token is not found in the database, check if it is still active
        if (is_null($token)) {
            try {
                $tokenInfo = $this->oidcService->introspect($credentials['token']);

                if (
                    ! empty($tokenInfo['active'])
                    && isset($tokenInfo['iat'], $tokenInfo['exp'])
                    && is_numeric($tokenInfo['iat'])
                    && is_numeric($tokenInfo['exp'])
                    && (int) $tokenInfo['exp'] > $now->getTimestamp()
                    && (! isset($tokenInfo['sub']) || (string) $tokenInfo['sub'] === $ssoId)
                    && (! isset($tokenInfo['uid']) || (string) $tokenInfo['uid'] === $tokenUid)
                ) {
                    $token = Token::withTrashed()->createOrFirst(
                        ['sso_id' => $tokenUid],
                        [
                            'user_id' => $user->id,
                            'last_used_at' => $now,
                            'created_at' => Carbon::createFromTimestamp((int) $tokenInfo['iat']),
                            'expires_at' => Carbon::createFromTimestamp((int) $tokenInfo['exp']),
                        ],
                    );
                } else {
                    return false;
                }
            } catch (Throwable $e) {
                return false;
            }
        }

        if (
            $token->trashed()
            || $token->user_id !== $user->id
            || ($token->expires_at !== null && $token->expires_at->lte($now))
        ) {
            return false;
        }

        if ($shouldBindSsoId) {
            try {
                $updated = User::query()
                    ->whereKey($user->id)
                    ->where(static function ($query): void {
                        $query->whereNull('sso_id')->orWhere('sso_id', '');
                    })
                    ->toBase()
                    ->update(['sso_id' => $ssoId]);
            } catch (UniqueConstraintViolationException) {
                return false;
            }

            if ($updated === 0 && User::whereKey($user->id)->value('sso_id') !== $ssoId) {
                return false;
            }

            $user->sso_id = $ssoId;
            $user->syncOriginalAttribute('sso_id');

            $groupIds = $user->groups()->pluck('groups.id')->all();

            if ($groupIds !== []) {
                Group::whereIn('id', $groupIds)->update(['updated_at' => $now]);
            }
        }

        if (
            ! $token->wasRecentlyCreated
            && (is_null($token->last_used_at) || $token->last_used_at->lte($now->copy()->subSeconds(60)))
        ) {
            $token->last_used_at = $now;
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
     * Get the access token from the current request strictly at offset 0.
     */
    private function getAccessToken(): ?string
    {
        $values = $this->request->headers->all('Authorization');

        if (count($values) !== 1 || ! is_string($values[0])) {
            return null;
        }

        $authorization = trim($values[0]);

        if (preg_match('/\ABearer[ \t]+([^\s,]+)\z/i', $authorization, $matches) !== 1) {
            return null;
        }

        return $matches[1];
    }
}
