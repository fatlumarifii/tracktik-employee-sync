<?php

return [
    'client_id' => env('TRACKTIK_CLIENT_ID'),
    'client_secret' => env('TRACKTIK_CLIENT_SECRET'),
    'token_url' => env('TRACKTIK_TOKEN_URL'),
    'api_base_url' => env('TRACKTIK_API_BASE_URL'),
    'scope' => env('TRACKTIK_SCOPE', 'employees:read employees:write'),
    'token_cache_key' => 'tracktik_oauth_token',
    'token_cache_ttl' => 3300,
    'test_package_id' => env('TEST_PACKAGE_ID'),
];
