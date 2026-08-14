<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Fathom API Key
    |--------------------------------------------------------------------------
    |
    | Your Fathom API key. Generate one at:
    | https://fathom.video/customize#api-access-header
    |
    */

    'api_key' => env('FATHOM_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL for the Fathom External API.
    |
    */

    'base_url' => env(
        'FATHOM_BASE_URL',
        'https://api.fathom.ai/external/v1'
    ),

    /*
    |--------------------------------------------------------------------------
    | HTTP Timeout
    |--------------------------------------------------------------------------
    |
    | The number of seconds to wait before timing out HTTP requests.
    |
    */

    'timeout' => env('FATHOM_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Retry Configuration
    |--------------------------------------------------------------------------
    |
    | Configure automatic retries for failed HTTP requests. The package does
    | not automatically retry rate-limited (429) responses.
    |
    */

    'retry' => [
        'times' => 3,
        'sleep' => 500,
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for verifying incoming Fathom webhook requests.
    |
    */

    'webhook' => [
        'secret' => env('FATHOM_WEBHOOK_SECRET'),
        'tolerance' => 300,
    ],

];
