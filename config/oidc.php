<?php

return [

    /*
    |--------------------------------------------------------------------------
    | OpenID Connect Client
    |--------------------------------------------------------------------------
    |
    | This option defines the OpenID Connect client configuration.
    |
    */

    'issuer' => env('OIDC_ISSUER', 'https://auth.infiniteuny.id/'),

    'configurations_uri' => env('OIDC_CONFIGURATIONS_URI', 'https://auth.infiniteuny.id/application/o/infinity/.well-known/openid-configuration'),

    'jwks_uri' => env('OIDC_JWKS_URI', 'https://auth.infiniteuny.id/application/o/infinity/jwks/'),

    'authorization_endpoint' => env('OIDC_AUTHORIZATION_ENDPOINT', 'https://auth.infiniteuny.id/application/o/infinity/authorize/'),

    'client_id' => env('OIDC_CLIENT_ID'),

    'client_secret' => env('OIDC_CLIENT_SECRET'),

];
