<?php

return [

    /*
    |--------------------------------------------------------------------------
    | X API Base URL
    |--------------------------------------------------------------------------
    */

    'base_url' => env('X_API_BASE_URL', 'https://api.x.com'),

    /*
    |--------------------------------------------------------------------------
    | HTTP Timeout
    |--------------------------------------------------------------------------
    */

    'timeout' => env('X_API_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | App-Only Bearer Token
    |--------------------------------------------------------------------------
    |
    | Used for app-only authentication (read-only public data).
    |
    */

    'bearer_token' => env('X_BEARER_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | OAuth 2.0 Client Credentials
    |--------------------------------------------------------------------------
    |
    | Used for token refresh flow.
    |
    */

    'client_id' => env('X_CLIENT_ID'),
    'client_secret' => env('X_CLIENT_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | OAuth 2.0 Redirect URI
    |--------------------------------------------------------------------------
    */

    'redirect_uri' => env('X_REDIRECT_URI', config('app.url').'/x/callback'),

    /*
    |--------------------------------------------------------------------------
    | Token Refreshed Callback
    |--------------------------------------------------------------------------
    |
    | When set, this callback is invoked after a token refresh with the new
    | credentials array: ['access_token', 'refresh_token', 'expires_at'].
    | Configure this in your AppServiceProvider to persist refreshed tokens.
    |
    */

    'token_refreshed' => null,

];
