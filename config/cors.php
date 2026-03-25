<?php
return [
    'paths' => ['api/*', 'user/*', 'sanctum/csrf-cookie'],  // rutas donde habilitar CORS
    'allowed_methods' => ['*'],
    'allowed_origins' => ['http://localhost:3000'],  // o ['*'] para desarrollo
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];

