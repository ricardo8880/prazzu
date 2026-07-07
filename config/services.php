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

    'chat_api' => [
        'url' => env('CHAT_API_URL', 'http://localhost/chat-api/public/perguntar'),
        'sistema' => env('CHAT_SISTEMA_SLUG', 'prazzu'),
        'assunto' => env('CHAT_ASSUNTO_SLUG', ''),
    ],

    'asaas' => [
        'base_url' => env('ASAAS_BASE_URL', 'https://api-sandbox.asaas.com/v3'),
        'api_key' => env('ASAAS_API_KEY'),
        'webhook_token' => env('ASAAS_WEBHOOK_TOKEN'),
        'timeout' => env('ASAAS_TIMEOUT', 30),
        'billing_type' => env('ASAAS_BILLING_TYPE', 'UNDEFINED'),
        'webhook_events' => [
            'PAYMENT_CREATED',
            'PAYMENT_UPDATED',
            'PAYMENT_CONFIRMED',
            'PAYMENT_RECEIVED',
            'PAYMENT_OVERDUE',
            'PAYMENT_DELETED',
            'PAYMENT_REFUNDED',
            'PAYMENT_CHARGEBACK_REQUESTED',
            'PAYMENT_CHARGEBACK_DISPUTE',
            'PAYMENT_AWAITING_CHARGEBACK_REVERSAL',
            'SUBSCRIPTION_CREATED',
            'SUBSCRIPTION_UPDATED',
            'SUBSCRIPTION_DELETED',
            'SUBSCRIPTION_INACTIVATED',
        ],
    ],

    'clicksign' => [
        'base_url' => env('CLICKSIGN_BASE_URL', 'https://app.clicksign.com'),
        'access_token' => env('CLICKSIGN_ACCESS_TOKEN'),
    ],

];
