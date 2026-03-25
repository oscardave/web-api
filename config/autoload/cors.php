<?php

declare(strict_types=1);

return [
    // Comma-separated list of allowed origins for CORS.
    // Only exact matches are allowed; wildcard '*' is NOT supported.
    // Example: "https://www.example.com,https://admin.example.com"
    'allowed_origins' => env('CORS_ALLOWED_ORIGINS', ''),
];
