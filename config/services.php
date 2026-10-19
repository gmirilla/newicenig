<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // Paystack credentials are managed at Admin → Membership → Paystack Settings (App\Settings\PaystackSettings),
    // not here. PAYSTACK_* env vars only seed the initial values the first time that settings migration runs.

    // A "dead man's switch" URL (e.g. a healthchecks.io check) pinged every
    // minute by App\Console\Commands\PingSchedulerHeartbeat, scheduled in
    // routes/console.php. It's the only way to detect cron dying entirely —
    // nothing scheduled BY cron can notice cron itself has stopped; the
    // monitoring service alerts instead when the ping goes silent. Left
    // blank, the heartbeat command is skipped (no-op), so this is opt-in.
    'scheduler_heartbeat' => [
        'url' => env('SCHEDULE_HEARTBEAT_URL'),
    ],

];
