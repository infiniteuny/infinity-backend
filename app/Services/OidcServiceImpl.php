<?php

namespace App\Services;

use App\Repositories\PsrCacheRepository;
use Facile\JoseVerifier\AccessTokenVerifierBuilder;
use Facile\JoseVerifier\JWK\JwksProviderBuilder;
use Facile\JoseVerifier\TokenVerifierInterface;
use Facile\OpenIDClient\Client\ClientBuilder;
use Facile\OpenIDClient\Client\ClientInterface;
use Facile\OpenIDClient\Client\Metadata\ClientMetadata;
use Facile\OpenIDClient\Client\Metadata\ClientMetadataInterface;
use Facile\OpenIDClient\Issuer\IssuerBuilder;
use Facile\OpenIDClient\Issuer\IssuerInterface;
use Facile\OpenIDClient\Issuer\Metadata\IssuerMetadata;
use Facile\OpenIDClient\Issuer\Metadata\IssuerMetadataInterface;
use Facile\OpenIDClient\Issuer\Metadata\Provider\MetadataProviderBuilder;
use Facile\OpenIDClient\Service\Builder\IntrospectionServiceBuilder;
use Facile\OpenIDClient\Service\Builder\UserInfoServiceBuilder;
use Facile\OpenIDClient\Service\IntrospectionService;
use Facile\OpenIDClient\Service\UserInfoService;
use Facile\OpenIDClient\Token\TokenSet;

class OidcServiceImpl implements OidcService
{
    protected IssuerMetadataInterface $issuerMetadata;

    protected ClientMetadataInterface $clientMetadata;

    protected IssuerInterface $issuer;

    protected ClientInterface $client;

    protected TokenVerifierInterface $accessTokenVerifier;

    protected IntrospectionService $introspectionService;

    protected UserInfoService $userInfoService;

    public function __construct(
        private PsrCacheRepository $cacheRepository,
    ) {
        $this->getIssuerMetadata();
        $this->getClientMetadata();
        $this->getIssuer();
        $this->getClient();
        $this->getAccessTokenVerifier();
        $this->getIntrospectionService();
        $this->getUserInfoService();
    }

    protected function getMetadataProviderBuilder(): MetadataProviderBuilder
    {
        $builder = new MetadataProviderBuilder;
        $builder->setCache($this->cacheRepository);
        // Cache metadata for 30 days
        $builder->setCacheTtl(2592000);

        return $builder;
    }

    protected function getJwksProviderBuilder(): JwksProviderBuilder
    {
        $builder = new JwksProviderBuilder;
        $builder->setCache($this->cacheRepository);
        // Cache JWKS for 1 day
        $builder->setCacheTtl(86400);

        return $builder;
    }

    protected function getIssuerBuilder(): IssuerBuilder
    {
        $builder = new IssuerBuilder;
        $builder->setMetadataProviderBuilder($this->getMetadataProviderBuilder());
        $builder->setJwksProviderBuilder($this->getJwksProviderBuilder());

        return $builder;
    }

    protected function getClientBuilder(): ClientBuilder
    {
        $builder = new ClientBuilder;
        $builder->setIssuer($this->getIssuer());
        $builder->setClientMetadata($this->getClientMetadata());

        return $builder;
    }

    protected function getAccessTokenVerifierBuilder(): AccessTokenVerifierBuilder
    {
        $builder = new AccessTokenVerifierBuilder;
        $builder->setIssuerMetadata($this->getIssuerMetadata()->toArray());
        $builder->setClientMetadata($this->getClientMetadata()->toArray());
        $builder->setJwksProviderBuilder($this->getJwksProviderBuilder());

        return $builder;
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
        if (! empty($this->issuerMetadata)) {
            return $this->issuerMetadata;
        }

        return $this->issuerMetadata = IssuerMetadata::fromArray([
            'issuer' => config('oidc.issuer'),
            'jwks_uri' => config('oidc.jwks_uri'),
            'authorization_endpoint' => config('oidc.authorization_endpoint'),
        ]);
    }

    protected function getClientMetadata(): ClientMetadataInterface
    {
        if (! empty($this->clientMetadata)) {
            return $this->clientMetadata;
        }

        return $this->clientMetadata = ClientMetadata::fromArray([
            'client_id' => config('oidc.client_id'),
            'client_secret' => config('oidc.client_secret'),
        ]);
    }

    protected function getIssuer(): IssuerInterface
    {
        if (! empty($this->issuer)) {
            return $this->issuer;
        }

        return $this->issuer = $this->getIssuerBuilder()->build(config('oidc.configurations_uri'));
    }

    protected function getClient(): ClientInterface
    {
        if (! empty($this->client)) {
            return $this->client;
        }

        return $this->client = $this->getClientBuilder()->build();
    }

    protected function getAccessTokenVerifier(): TokenVerifierInterface
    {
        if (! empty($this->accessTokenVerifier)) {
            return $this->accessTokenVerifier;
        }

        return $this->accessTokenVerifier = $this->getAccessTokenVerifierBuilder()->build();
    }

    protected function getIntrospectionService(): IntrospectionService
    {
        if (! empty($this->introspectionService)) {
            return $this->introspectionService;
        }

        return $this->introspectionService = $this->getIntrospectionServiceBuilder()->build();
    }

    protected function getUserInfoService(): UserInfoService
    {
        if (! empty($this->userInfoService)) {
            return $this->userInfoService;
        }

        return $this->userInfoService = $this->getUserInfoServiceBuilder()->build();
    }

    /**
     * @throws InvalidTokenException
     */
    public function verify(string $token): array
    {
        $verifier = $this->getAccessTokenVerifier();

        return $verifier->verify($token);
    }

    public function introspect(string $token): array
    {
        $service = $this->getIntrospectionService();

        return $service->introspect($this->client, $token);
    }

    public function getUserInfo(string $token): array
    {
        $service = $this->getUserInfoService();

        return $service->getUserInfo($this->client, TokenSet::fromParams(['access_token' => $token]));
    }
}
