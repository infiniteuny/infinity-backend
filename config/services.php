<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'authentik' => [
        'base_url' => env('AUTHENTIK_BASE_URL', 'https://auth.infiniteuny.id'),
        'token' => env('AUTHENTIK_TOKEN'),
        'core_team_group_id' => env('AUTHENTIK_CORE_TEAM_GROUP_ID'),
        'xcore_team_group_id' => env('AUTHENTIK_XCORE_TEAM_GROUP_ID'),
        'cg_admin_group_id' => env('AUTHENTIK_CG_ADMIN_GROUP_ID'),
        'xcg_admin_group_id' => env('AUTHENTIK_XCG_ADMIN_GROUP_ID'),
        'member_group_id' => env('AUTHENTIK_MEMBER_GROUP_ID'),
        'admin_group_id' => env('AUTHENTIK_ADMIN_GROUP_ID'),
    ],

];
