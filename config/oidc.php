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

    'clock_tolerance' => (int) env('OIDC_CLOCK_TOLERANCE', 10),

    'http_timeout' => (int) env('OIDC_HTTP_TIMEOUT', 10),

    'memoize_ttl' => (int) env('OIDC_MEMOIZE_TTL', 300),

];
