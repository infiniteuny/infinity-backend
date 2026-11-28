<?php

namespace Tests\Unit\Services;

use App\Repositories\PsrCacheRepository;
use App\Services\OidcServiceImpl;
use App\Utils\Encoder;
use Facile\JoseVerifier\Exception\InvalidTokenClaimException;
use Facile\JoseVerifier\Exception\InvalidTokenException;
use Facile\JoseVerifier\JWK\MemoryJwksProvider;
use Facile\OpenIDClient\Issuer\Issuer;
use Facile\OpenIDClient\Issuer\IssuerInterface;
use Facile\OpenIDClient\Issuer\Metadata\IssuerMetadata;
use GuzzleHttp\Client as GuzzleClient;
use OpenSSLAsymmetricKey;
use ReflectionMethod;
use Tests\TestCase;

class OidcServiceImplTest extends TestCase
{
    private const ISSUER = 'https://auth.infiniteuny.id/application/o/infinity/';

    private const CLIENT_ID = 'infinity-client-id';

    private const CLIENT_SECRET = 'super-secret-client-secret-for-introspection-only';

    private OpenSSLAsymmetricKey $privateKey;

    private OidcServiceImpl $service;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'oidc.client_id' => self::CLIENT_ID,
            'oidc.client_secret' => self::CLIENT_SECRET,
        ]);

        $privateKey = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);
        $this->assertInstanceOf(OpenSSLAsymmetricKey::class, $privateKey);
        $this->privateKey = $privateKey;

        $details = openssl_pkey_get_details($this->privateKey);
        $this->assertIsArray($details);

        // Intentionally omit 'alg' on the JWK (RFC 7517 optional), which previously let Jose's None::verify() succeed
        $rsaJwk = [
            'kty' => 'RSA',
            'use' => 'sig',
            'kid' => 'authentik-rsa-1',
            'n' => Encoder::base64UrlEncode($details['rsa']['n']),
            'e' => Encoder::base64UrlEncode($details['rsa']['e']),
        ];

        $issuer = new Issuer(
            IssuerMetadata::fromArray([
                'issuer' => self::ISSUER,
                'authorization_endpoint' => self::ISSUER.'authorize/',
                'jwks_uri' => self::ISSUER.'jwks/',
                'response_types_supported' => ['code'],
            ]),
            new MemoryJwksProvider(['keys' => [$rsaJwk]]),
        );

        $cache = $this->app->make(PsrCacheRepository::class);

        $this->service = new class($cache, $issuer) extends OidcServiceImpl
        {
            public function __construct(PsrCacheRepository $cache, private IssuerInterface $stubIssuer)
            {
                parent::__construct($cache);
            }

            protected function getIssuer(): IssuerInterface
            {
                return $this->stubIssuer;
            }
        };
    }

    public function test_verify_accepts_valid_asymmetric_rs256_jwt_and_preserves_client_secret_on_oauth_client(): void
    {
        $now = time();
        $payload = [
            'iss' => self::ISSUER,
            'sub' => '101',
            'aud' => self::CLIENT_ID,
            'iat' => $now - 5,
            'exp' => $now + 3600,
            'uid' => 'tok-uuid-1',
        ];

        $headerSegment = Encoder::base64UrlEncode(json_encode([
            'alg' => 'RS256',
            'typ' => 'JWT',
            'kid' => 'authentik-rsa-1',
        ], JSON_THROW_ON_ERROR));
        $payloadSegment = Encoder::base64UrlEncode(json_encode($payload, JSON_THROW_ON_ERROR));
        $signingInput = "{$headerSegment}.{$payloadSegment}";

        $signature = '';
        openssl_sign($signingInput, $signature, $this->privateKey, OPENSSL_ALGO_SHA256);
        $jwt = "{$signingInput}.".Encoder::base64UrlEncode($signature);

        $verified = $this->service->verify($jwt);

        $this->assertSame('101', $verified['sub']);
        $this->assertSame('tok-uuid-1', $verified['uid']);

        $getClient = new ReflectionMethod($this->service, 'getClient');
        $this->assertSame(self::CLIENT_SECRET, $getClient->invoke($this->service)->getMetadata()->getClientSecret());
    }

    public function test_verify_rejects_unsigned_alg_none_jwt(): void
    {
        $now = time();
        $headerSegment = Encoder::base64UrlEncode(json_encode([
            'alg' => 'none',
            'typ' => 'JWT',
        ], JSON_THROW_ON_ERROR));
        $payloadSegment = Encoder::base64UrlEncode(json_encode([
            'iss' => self::ISSUER,
            'sub' => '101',
            'aud' => self::CLIENT_ID,
            'iat' => $now - 5,
            'exp' => $now + 3600,
            'uid' => 'tok-uuid-1',
        ], JSON_THROW_ON_ERROR));

        $this->expectException(InvalidTokenException::class);
        $this->service->verify("{$headerSegment}.{$payloadSegment}.");
    }

    public function test_verify_and_underlying_verifier_reject_hs256_symmetric_client_secret_confusion(): void
    {
        $now = time();
        $headerSegment = Encoder::base64UrlEncode(json_encode([
            'alg' => 'HS256',
            'typ' => 'JWT',
        ], JSON_THROW_ON_ERROR));
        $payloadSegment = Encoder::base64UrlEncode(json_encode([
            'iss' => self::ISSUER,
            'sub' => '101',
            'aud' => self::CLIENT_ID,
            'iat' => $now - 5,
            'exp' => $now + 3600,
            'uid' => 'tok-uuid-1',
        ], JSON_THROW_ON_ERROR));
        $signingInput = "{$headerSegment}.{$payloadSegment}";
        $hmac = hash_hmac('sha256', $signingInput, self::CLIENT_SECRET, true);
        $forgedHs256Jwt = "{$signingInput}.".Encoder::base64UrlEncode($hmac);

        try {
            $this->service->verify($forgedHs256Jwt);
            $this->fail('Expected HS256 JWT to be rejected by OidcServiceImpl::verify()');
        } catch (InvalidTokenException $e) {
            $this->assertStringContainsString('algorithm', $e->getMessage());
        }

        // Defense-in-depth: underlying verifier was constructed without client_secret
        $getVerifier = new ReflectionMethod($this->service, 'getAccessTokenVerifier');
        $verifier = $getVerifier->invoke($this->service);

        $this->expectException(InvalidTokenException::class);
        $verifier->verify($forgedHs256Jwt);
    }

    public function test_verify_rejects_malformed_or_missing_alg_tokens(): void
    {
        $payloadSegment = Encoder::base64UrlEncode('{"sub":"101"}');
        $missingAlgHeader = Encoder::base64UrlEncode('{"typ":"JWT"}');

        foreach ([
            '',
            'not-a-jwt',
            'a.b.c.d.e',
            "{$missingAlgHeader}.{$payloadSegment}.sig",
        ] as $invalidToken) {
            try {
                $this->service->verify($invalidToken);
                $this->fail("Expected invalid token [{$invalidToken}] to throw InvalidTokenException");
            } catch (InvalidTokenException) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function test_applies_clock_tolerance_http_timeout_and_expires_in_memory_memoization_after_ttl(): void
    {
        config([
            'oidc.clock_tolerance' => 10,
            'oidc.http_timeout' => 12,
            'oidc.memoize_ttl' => 300,
        ]);

        $now = time();
        $signJwt = function (int $exp) use ($now): string {
            $headerSegment = Encoder::base64UrlEncode(json_encode([
                'alg' => 'RS256',
                'typ' => 'JWT',
                'kid' => 'authentik-rsa-1',
            ], JSON_THROW_ON_ERROR));
            $payloadSegment = Encoder::base64UrlEncode(json_encode([
                'iss' => self::ISSUER,
                'sub' => '101',
                'aud' => self::CLIENT_ID,
                'iat' => $now - 30,
                'exp' => $exp,
                'uid' => 'tok-skew',
            ], JSON_THROW_ON_ERROR));
            $signingInput = "{$headerSegment}.{$payloadSegment}";
            $signature = '';
            openssl_sign($signingInput, $signature, $this->privateKey, OPENSSL_ALGO_SHA256);

            return "{$signingInput}.".Encoder::base64UrlEncode($signature);
        };

        // Expired 5s ago passes under 10s clock_tolerance
        $verified = $this->service->verify($signJwt($now - 5));
        $this->assertSame('101', $verified['sub']);

        // Expired 25s ago fails under 10s clock_tolerance
        try {
            $this->service->verify($signJwt($now - 25));
            $this->fail('Expected JWT expired beyond clock_tolerance to fail');
        } catch (InvalidTokenClaimException) {
            $this->addToAssertionCount(1);
        }

        // PSR-18 client carries configured connect_timeout and timeout
        $getHttpClient = new ReflectionMethod($this->service, 'getHttpClient');
        $httpClient = $getHttpClient->invoke($this->service);
        $this->assertInstanceOf(GuzzleClient::class, $httpClient);
        $this->assertSame(5, $httpClient->getConfig('connect_timeout'));
        $this->assertSame(12, $httpClient->getConfig('timeout'));

        // Memoized client & verifier stay identical within TTL, then refresh once TTL elapses
        $getClient = new ReflectionMethod($this->service, 'getClient');
        $getVerifier = new ReflectionMethod($this->service, 'getAccessTokenVerifier');

        $clientBefore = $getClient->invoke($this->service);
        $verifierBefore = $getVerifier->invoke($this->service);

        $this->travel(120)->seconds();
        $this->assertSame($clientBefore, $getClient->invoke($this->service));
        $this->assertSame($verifierBefore, $getVerifier->invoke($this->service));

        $this->travel(200)->seconds();
        $this->assertNotSame($clientBefore, $getClient->invoke($this->service));
        $this->assertNotSame($verifierBefore, $getVerifier->invoke($this->service));
    }
}
