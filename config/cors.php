<?php

$defaultOrigins = [
    'http://localhost:3000',
    'http://127.0.0.1:3000',
];

$envOrigins = array_filter(array_map('trim', explode(',', (string) env('CORS_ALLOWED_ORIGINS', ''))));
$frontendUrls = array_filter(array_map('trim', explode(',', (string) env('FRONTEND_URLS', env('FRONTEND_URL', '')))));

return [
    'paths' => ['api/*', 'user/*', 'cliente/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => array_values(array_unique(array_merge($defaultOrigins, $envOrigins, $frontendUrls))),
    'allowed_origins_patterns' => array_values(array_filter(array_map('trim', explode(',', (string) env('CORS_ALLOWED_ORIGIN_PATTERNS', ''))))),
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 3600,
    'supports_credentials' => false,
];
