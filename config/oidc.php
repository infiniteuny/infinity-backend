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

    'configurations_uri' => env('OIDC_CONFIGURATIONS_URI', 'https://auth.infiniteuny.id/application/o/infinity/.well-known/openid-configuration'),

    'client_id' => env('OIDC_CLIENT_ID'),

    'client_secret' => env('OIDC_CLIENT_SECRET'),

];
