<?php

namespace App\Guards;

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

class OidcHeadersGuard implements Guard
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
        if (empty($credentials['headers'])) {
            if (! $credentials['headers'] = $this->getCredentialHeaders()) {
                return false;
            }
        }

        $ssoId = (string) $credentials['headers']['user_id'];
        $tokenId = (string) $credentials['headers']['token_id'];
        $shouldBindSsoId = false;

        // Check if the token subject exists in the users database
        $user = $this->provider->retrieveByCredentials(['sso_id' => $ssoId]);

        // If the user is not found, try to match an unbound user by case-insensitive email
        if (is_null($user)) {
            try {
                $email = mb_strtolower(trim((string) $credentials['headers']['user_email']), 'UTF-8');

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
        $token = Token::withTrashed()->where('sso_id', $tokenId)->first();
        $now = Carbon::now();

        // If the token is not found in the database, check if it is still active and matches the claimed subject
        if (is_null($token)) {
            try {
                $tokenInfo = $this->oidcService->introspect($credentials['headers']['token']);

                if (
                    ! empty($tokenInfo['active'])
                    && isset($tokenInfo['iat'], $tokenInfo['exp'], $tokenInfo['sub'])
                    && is_numeric($tokenInfo['iat'])
                    && is_numeric($tokenInfo['exp'])
                    && (int) $tokenInfo['exp'] > $now->getTimestamp()
                    && (string) $tokenInfo['sub'] === $ssoId
                    && (! isset($tokenInfo['uid']) || (string) $tokenInfo['uid'] === $tokenId)
                ) {
                    $token = Token::withTrashed()->createOrFirst(
                        ['sso_id' => $tokenId],
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
        }

        if (! $token->wasRecentlyCreated) {
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
     * Get the credential headers from the current request.
     */
    private function getCredentialHeaders(): ?array
    {
        $userId = $this->getSingleHeader('X-User-Id');
        $userEmail = $this->getSingleHeader('X-User-Email');
        $tokenId = $this->getSingleHeader('X-Token-Id');
        $tokenCreatedAt = $this->getSingleHeader('X-Token-Created-At');
        $tokenExpiresAt = $this->getSingleHeader('X-Token-Expires-At');
        $token = $this->getStrictBearerToken();

        if (
            $userId === null
            || $userEmail === null
            || $tokenId === null
            || $tokenCreatedAt === null
            || $tokenExpiresAt === null
            || $token === null
            || ! ctype_digit($tokenCreatedAt)
            || ! ctype_digit($tokenExpiresAt)
            || (int) $tokenExpiresAt <= Carbon::now()->getTimestamp()
        ) {
            return null;
        }

        return [
            'user_id' => $userId,
            'user_email' => $userEmail,
            'token_id' => $tokenId,
            'token_created_at' => $tokenCreatedAt,
            'token_expires_at' => $tokenExpiresAt,
            'token' => $token,
        ];
    }

    private function getSingleHeader(string $name): ?string
    {
        $values = $this->request->headers->all($name);

        if (count($values) !== 1 || ! is_string($values[0])) {
            return null;
        }

        $trimmed = trim($values[0]);

        return $trimmed !== '' ? $trimmed : null;
    }

    /**
     * Extract a Bearer token strictly at offset 0 so non-Bearer prefixes
     * (e.g. "Token Bearer ...") cannot bypass Envoy's jwt_authn extractor.
     */
    private function getStrictBearerToken(): ?string
    {
        $authorization = $this->getSingleHeader('Authorization');

        if ($authorization === null) {
            return null;
        }

        if (preg_match('/\ABearer[ \t]+([^\s,]+)\z/i', $authorization, $matches) !== 1) {
            return null;
        }

        return $matches[1];
    }
}
