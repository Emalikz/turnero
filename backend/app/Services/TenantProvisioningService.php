<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

final class TenantProvisioningService
{
    public function create(array $attributes): Tenant
    {
        return DB::transaction(function () use ($attributes): Tenant {
            $slug = (string) $attributes['slug'];
            $schema = $this->schemaNameFromSlug($slug);

            $tenant = Tenant::query()->create([
                'name' => $attributes['name'],
                'slug' => $slug,
                'schema' => $schema,
                'primary_domain' => $attributes['primary_domain'],
                'settings' => Arr::get($attributes, 'settings'),
            ]);

            $this->createSchemaIfNeeded($schema);

            return $tenant;
        });
    }

    private function createSchemaIfNeeded(string $schema): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement(sprintf('create schema if not exists "%s"', str_replace('"', '', $schema)));
    }

    private function schemaNameFromSlug(string $slug): string
    {
        $prefix = (string) config('tenancy.database.tenant_schema_prefix', 'tenant_');

        return $prefix.str_replace('-', '_', $slug);
    }
}
