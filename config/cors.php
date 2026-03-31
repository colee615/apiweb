<?php

return [
    'paths' => ['api/*', 'user/*', 'cliente/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => array_values(array_filter(array_map('trim', explode(',', (string) env('CORS_ALLOWED_ORIGINS', 'http://localhost:3000'))))),
    'allowed_origins_patterns' => array_values(array_filter(array_map('trim', explode(',', (string) env('CORS_ALLOWED_ORIGIN_PATTERNS', ''))))),
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 3600,
    'supports_credentials' => false,
];
