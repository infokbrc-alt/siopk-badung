<?php

return [

    /*
    |------------------------------------------------------------------
    | Claude AI (Anthropic)
    |------------------------------------------------------------------
    | Tambahkan ke .env:
    |   CLAUDE_API_KEY=sk-ant-api03-xxxxxxxxx
    */
    'claude' => [
        'api_key' => env('CLAUDE_API_KEY', ''),
    ],

    /*
    |------------------------------------------------------------------
    | WhatsApp Notification (opsional, Fase 7)
    |------------------------------------------------------------------
    */
    'whatsapp' => [
        'token'    => env('WHATSAPP_TOKEN', ''),
        'phone_id' => env('WHATSAPP_PHONE_ID', ''),
    ],

    /*
    | Layanan lain (bawaan Laravel)
    */
    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],
    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    'resend' => [
        'key' => env('RESEND_KEY'),
    ],
    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel'              => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
