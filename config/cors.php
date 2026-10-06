<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    // Comma-separated extra origins. The app URL itself is always allowed.
    // Mobile apps (token auth) are not CORS-constrained; this governs
    // browser-based API consumers only. Never use `*` with credentials.
    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
    'allowed_origins' => array_values(array_filter(array_unique(array_merge(
        [(string) env('APP_URL', '')],
        explode(',', (string) env('CORS_ALLOWED_ORIGINS', ''))
    )))),
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['Content-Type', 'Accept', 'Authorization', 'X-Requested-With', 'X-Request-ID', 'Idempotency-Key', 'X-Device-ID', 'X-Scanned-By'],
    'exposed_headers' => ['X-Request-ID'],
    'max_age' => 86400,
    'supports_credentials' => true,
];
