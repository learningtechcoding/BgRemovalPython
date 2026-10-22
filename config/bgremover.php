<?php

return [
    // Active engine: "python" (self-hosted rembg service) or "removebg" (remove.bg API)
    'provider' => env('BG_PROVIDER', 'python'),

    'python' => [
        'url' => env('BG_PYTHON_URL', 'http://127.0.0.1:8001'),
        'token' => env('BG_PYTHON_TOKEN', 'local-dev-token'),
        'timeout' => (int) env('BG_PYTHON_TIMEOUT', 180),
    ],

    'removebg' => [
        'key' => env('REMOVEBG_API_KEY'),
        'timeout' => (int) env('REMOVEBG_TIMEOUT', 90),
    ],

    'limits' => [
        'max_file_kb' => (int) env('BG_MAX_FILE_KB', 10240),
        'max_dimension' => (int) env('BG_MAX_DIMENSION', 8000),
        'min_dimension' => 16,
        'max_pixels' => (int) env('BG_MAX_PIXELS', 25000000),
        'uploads_per_hour' => (int) env('BG_UPLOADS_PER_HOUR', 30),
    ],

    // Hours to keep uploads/results before they are deleted.
    'retention_hours' => (int) env('BG_RETENTION_HOURS', 1),

    'contact_email' => env('BG_CONTACT_EMAIL', 'support@example.com'),
];
