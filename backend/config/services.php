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

    // ──────────────────────────────────────────────────────────────────────────
    // FACTURACIÓN ELECTRÓNICA — DGI Panamá
    // ──────────────────────────────────────────────────────────────────────────

    'fe' => [
        // 'sandbox' | 'production'
        'ambiente' => env('FE_AMBIENTE', 'sandbox'),
    ],

    // The Factory HKA (PAC primario)
    'tfhka' => [
        'wsdl_sandbox' => env(
            'TFHKA_WSDL_SANDBOX',
            'https://demows.epak.com.pa/WsIntegracion_GT/WsIntegracion.asmx?WSDL'
        ),
        'wsdl_prod' => env(
            'TFHKA_WSDL_PROD',
            'https://ws.epak.com.pa/WsIntegracion_GT/WsIntegracion.asmx?WSDL'
        ),
    ],

    // Digifact (PAC secundario)
    'digifact' => [
        'api_sandbox' => env('DIGIFACT_API_SANDBOX', 'https://api-pruebas.digifact.com.pa'),
        'api_prod'    => env('DIGIFACT_API_PROD', 'https://api.digifact.com.pa'),
    ],

];
