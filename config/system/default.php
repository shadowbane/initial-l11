<?php

return [
    'redirect_https' => env('REDIRECT_HTTPS', false),
    'encryption_key' => env('ENCRYPTION_KEY', ''),
    'encryption_iv' => env('ENCRYPTION_IV', ''),

    /*
    |--------------------------------------------------------------------------
    | Client Info
    |--------------------------------------------------------------------------
    |
    */
    'load_config_from_database' => env('LOAD_CONFIG_FROM_DB', true),
    'users' => [
        'allow_email_change' => false,
    ],
];
