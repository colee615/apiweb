<?php

$defaultOrigins = [
    'http://localhost:3000',
    'http://127.0.0.1:3000',
];

$envOrigins = array_filter(array_map('trim', explode(',', (string) env('CORS_ALLOWED_ORIGINS', ''))));
$frontendUrls = array_filter(array_map('trim', explode(',', (string) env('FRONTEND_URLS', env('FRONTEND_URL', '')))));
$configuredPatterns = array_values(array_filter(array_map('trim', explode(',', (string) env('CORS_ALLOWED_ORIGIN_PATTERNS', '')))));
$validPatternDelimiters = ['/', '#', '~', '%', '!'];

$validPatterns = array_values(array_filter($configuredPatterns, function ($pattern) use ($validPatternDelimiters) {
    if ($pattern === '') {
        return false;
    }

    $delimiter = substr($pattern, 0, 1);
    if (!in_array($delimiter, $validPatternDelimiters, true)) {
        return false;
    }

    return strrpos($pattern, $delimiter) > 0;
}));

$patternLikeOrigins = array_values(array_filter($configuredPatterns, fn ($pattern) => !in_array($pattern, $validPatterns, true)));

return [
    'paths' => ['api/*', 'user/*', 'cliente/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => array_values(array_unique(array_merge($defaultOrigins, $envOrigins, $frontendUrls, $patternLikeOrigins))),
    'allowed_origins_patterns' => $validPatterns,
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 3600,
    'supports_credentials' => false,
];
