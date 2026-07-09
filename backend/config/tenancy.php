<?php

declare(strict_types=1);

use App\Models\Tenant;

return [
    'tenant_model' => Tenant::class,
    'identifier_header' => env('TENANT_HEADER', 'X-Tenant'),
    'central_domains' => array_filter(array_map('trim', explode(',', (string) env('CENTRAL_DOMAINS', 'localhost,127.0.0.1')))),
    'database' => [
        'central_schema' => env('DB_CENTRAL_SCHEMA', 'public'),
        'tenant_schema_prefix' => env('DB_TENANT_SCHEMA_PREFIX', 'tenant_'),
    ],
];
