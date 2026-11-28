<?php

namespace App\Services;

use App\Repositories\PsrCacheRepository;
use Facile\JoseVerifier\Exception\InvalidTokenException;
use Facile\JoseVerifier\JWK\JwksProviderBuilder;
use Facile\JoseVerifier\TokenVerifierInterface;
use Facile\OpenIDClient\Client\ClientBuilder;
use Facile\OpenIDClient\Client\ClientInterface;
use Facile\OpenIDClient\Client\Metadata\ClientMetadata;
use Facile\OpenIDClient\Client\Metadata\ClientMetadataInterface;
use Facile\OpenIDClient\Issuer\IssuerBuilder;
use Facile\OpenIDClient\Issuer\IssuerInterface;
use Facile\OpenIDClient\Issuer\Metadata\IssuerMetadataInterface;
use Facile\OpenIDClient\Issuer\Metadata\Provider\MetadataProviderBuilder;
use Facile\OpenIDClient\Service\Builder\IntrospectionServiceBuilder;
use Facile\OpenIDClient\Service\Builder\UserInfoServiceBuilder;
use Facile\OpenIDClient\Service\IntrospectionService;
use Facile\OpenIDClient\Service\UserInfoService;
use Facile\OpenIDClient\Token\AccessTokenVerifierBuilder;
use Facile\OpenIDClient\Token\TokenSet;
use Jose\Component\Signature\Serializer\CompactSerializer;
use Throwable;

class OidcServiceImpl implements OidcService
{
    /**
     * Asymmetric JWS algorithms permitted for OIDC access token verification.
     *
     * @var list<string>
     */
    protected const ALLOWED_SIGNING_ALGORITHMS = [
        'RS256',
        'RS384',
        'RS512',
        'PS256',
        'PS384',
        'PS512',
        'ES256',
        'ES384',
        'ES512',
        'EdDSA',
    ];

    protected IssuerInterface $issuer;

    protected ClientInterface $client;

    protected TokenVerifierInterface $accessTokenVerifier;

    protected IntrospectionService $introspectionService;

    protected UserInfoService $userInfoService;

    public function __construct(
        private PsrCacheRepository $cacheRepository,
    ) {}

    protected function getMetadataProviderBuilder(): MetadataProviderBuilder
    {
        $builder = (new MetadataProviderBuilder)
            ->setCache($this->cacheRepository)
            // Cache metadata for 30 days
            ->setCacheTtl(2592000);

        return $builder;
    }

    protected function getJwksProviderBuilder(): JwksProviderBuilder
    {
        $builder = (new JwksProviderBuilder)
            ->withCache($this->cacheRepository)
            // Cache JWKS for 1 day
            ->withCacheTtl(86400);

        return $builder;
    }

    protected function getIssuerBuilder(): IssuerBuilder
    {
        $builder = (new IssuerBuilder)
            ->setMetadataProviderBuilder($this->getMetadataProviderBuilder())
            ->setJwksProviderBuilder($this->getJwksProviderBuilder());

        return $builder;
    }

    protected function getClientBuilder(): ClientBuilder
    {
        $builder = (new ClientBuilder)
            ->setIssuer($this->getIssuer())
            ->setClientMetadata(ClientMetadata::fromArray([
                'client_id' => config('oidc.client_id'),
                'client_secret' => config('oidc.client_secret'),
            ]));

        return $builder;
    }

    protected function getAccessTokenVerifierBuilder(): AccessTokenVerifierBuilder
    {
        return new AccessTokenVerifierBuilder;
    }

    protected function getIntrospectionServiceBuilder(): IntrospectionServiceBuilder
    {
        return new IntrospectionServiceBuilder;
    }

    protected function getUserInfoServiceBuilder(): UserInfoServiceBuilder
    {
        return new UserInfoServiceBuilder;
    }

    protected function getIssuerMetadata(): IssuerMetadataInterface
    {
        return $this->getIssuer()->getMetadata();
    }

    protected function getClientMetadata(): ClientMetadataInterface
    {
        return $this->getClient()->getMetadata();
    }

    protected function getIssuer(): IssuerInterface
    {
        if (! empty($this->issuer)) {
            return $this->issuer;
        }

        return $this->issuer = $this->getIssuerBuilder()
            ->build(config('oidc.configurations_uri'));
    }

    protected function getClient(): ClientInterface
    {
        if (! empty($this->client)) {
            return $this->client;
        }

        return $this->client = $this->getClientBuilder()
            ->build();
    }

    protected function getAccessTokenVerifier(): TokenVerifierInterface
    {
        if (! empty($this->accessTokenVerifier)) {
            return $this->accessTokenVerifier;
        }

        $verificationClient = (new ClientBuilder)
            ->setIssuer($this->getIssuer())
            ->setClientMetadata(ClientMetadata::fromArray([
                'client_id' => config('oidc.client_id'),
            ]))
            ->build();

        return $this->accessTokenVerifier = $this->getAccessTokenVerifierBuilder()
            ->build($verificationClient);
    }

    protected function getIntrospectionService(): IntrospectionService
    {
        if (! empty($this->introspectionService)) {
            return $this->introspectionService;
        }

        return $this->introspectionService = $this->getIntrospectionServiceBuilder()
            ->build();
    }

    protected function getUserInfoService(): UserInfoService
    {
        if (! empty($this->userInfoService)) {
            return $this->userInfoService;
        }

        return $this->userInfoService = $this->getUserInfoServiceBuilder()
            ->build();
    }

    /**
     * @throws InvalidTokenException
     */
    public function verify(string $token): array
    {
        $this->assertAllowedSigningAlgorithm($token);

        $verifier = $this->getAccessTokenVerifier();

        return $verifier->verify($token);
    }

    /**
     * @throws InvalidTokenException
     */
    protected function assertAllowedSigningAlgorithm(string $token): void
    {
        try {
            $jws = (new CompactSerializer)->unserialize($token);
        } catch (Throwable $e) {
            throw new InvalidTokenException('Invalid JWT provided', 0, $e);
        }

        if ($jws->countSignatures() !== 1) {
            throw new InvalidTokenException('Invalid JWT signature count');
        }

        $header = $jws->getSignature(0)->getProtectedHeader();
        $alg = $header['alg'] ?? null;

        if (! is_string($alg) || ! in_array($alg, self::ALLOWED_SIGNING_ALGORITHMS, true)) {
            throw new InvalidTokenException('Unsupported or disallowed JWT signing algorithm');
        }
    }

    public function introspect(string $token): array
    {
        $service = $this->getIntrospectionService();

        return $service->introspect($this->getClient(), $token);
    }

    public function getUserInfo(string $token): array
    {
        $service = $this->getUserInfoService();

        return $service->getUserInfo($this->getClient(), TokenSet::fromParams(['access_token' => $token]));
    }
}
