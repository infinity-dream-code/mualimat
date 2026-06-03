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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    "turnstile" => [
        "enabled" => env("TURNSTILE_ENABLED", true),
        "site_key" => env("TURNSTILE_SITE_KEY")
            ?: env(
                env("APP_ENV", "production") === "local"
                    ? "TURNSTILE_SITE_KEY_LOCAL"
                    : "TURNSTILE_SITE_KEY_PROD",
            ),
        "secret_key" => env("TURNSTILE_SECRET_KEY")
            ?: env(
                env("APP_ENV", "production") === "local"
                    ? "TURNSTILE_SECRET_KEY_LOCAL"
                    : "TURNSTILE_SECRET_KEY_PROD",
            ),
    ],

];
