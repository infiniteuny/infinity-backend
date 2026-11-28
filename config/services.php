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
        'base_url' => is_string($v = env('AUTHENTIK_BASE_URL', 'https://auth.infiniteuny.id')) ? trim($v) : $v,
        'token' => is_string($v = env('AUTHENTIK_TOKEN')) ? trim($v) : $v,
        'core_team_group_id' => is_string($v = env('AUTHENTIK_CORE_TEAM_GROUP_ID')) ? trim($v) : $v,
        'xcore_team_group_id' => is_string($v = env('AUTHENTIK_XCORE_TEAM_GROUP_ID')) ? trim($v) : $v,
        'cg_admin_group_id' => is_string($v = env('AUTHENTIK_CG_ADMIN_GROUP_ID')) ? trim($v) : $v,
        'xcg_admin_group_id' => is_string($v = env('AUTHENTIK_XCG_ADMIN_GROUP_ID')) ? trim($v) : $v,
        'member_group_id' => is_string($v = env('AUTHENTIK_MEMBER_GROUP_ID')) ? trim($v) : $v,
        'internal_group_id' => is_string($v = env('AUTHENTIK_INTERNAL_GROUP_ID')) ? trim($v) : $v,
        'admin_group_id' => is_string($v = env('AUTHENTIK_ADMIN_GROUP_ID')) ? trim($v) : $v,
    ],

    'outline' => [
        'base_url' => is_string($v = env('OUTLINE_BASE_URL', 'https://docs.infiniteuny.id')) ? trim($v) : $v,
        'token' => is_string($v = env('OUTLINE_TOKEN')) ? trim($v) : $v,
        'core_team_group_id' => is_string($v = env('OUTLINE_CORE_TEAM_GROUP_ID')) ? trim($v) : $v,
        'xcore_team_group_id' => is_string($v = env('OUTLINE_XCORE_TEAM_GROUP_ID')) ? trim($v) : $v,
    ],

];
