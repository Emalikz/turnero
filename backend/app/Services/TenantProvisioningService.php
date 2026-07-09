<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class TenantProvisioningService
{
    public function create(array $attributes): array
    {
        return DB::transaction(function () use ($attributes): array {
            $slug = (string) $attributes['slug'];
            $schema = $this->schemaNameFromSlug($slug);

            $tenant = Tenant::query()->create([
                'name' => $attributes['name'],
                'slug' => $slug,
                'schema' => $schema,
                'primary_domain' => $attributes['primary_domain'],
                'settings' => Arr::get($attributes, 'settings'),
            ]);

            $tenant->setInternal('db_name', $schema);
            $tenant->save();

            if (DB::getDriverName() === 'pgsql') {
                $this->provisionPostgresTenant($tenant);
            }

            $tempPassword = $slug . '@password';
            $adminUser = $this->createDefaultAdmin(
                schema: $schema,
                name: 'Admin',
                email: 'admin@turnero.com',
                password: $tempPassword,
            );

            return [
                'tenant' => $tenant,
                'admin_user' => $adminUser,
                'temp_password' => $tempPassword,
            ];
        });
    }

    private function provisionPostgresTenant(Tenant $tenant): void
    {
        $schema = $tenant->schema;
        $manager = $tenant->database()->manager();

        if (! $manager->databaseExists($schema)) {
            $manager->createDatabase($tenant);
        }

        $migrationFiles = [
            database_path('migrations/tenant/0001_create_users_table.php'),
            database_path('migrations/tenant/0002_create_password_reset_tokens_table.php'),
        ];

        foreach ($migrationFiles as $file) {
            if (! file_exists($file)) {
                continue;
            }

            /** @var Migration $migration */
            $migration = require $file;

            DB::statement('SET search_path TO "' . $schema . '"');
            $migration->up();
        }

        DB::statement('SET search_path TO "' . $schema . '", public');
    }

    private function createDefaultAdmin(string $schema, string $name, string $email, string $password): array
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::table($schema . '.users')->insert([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'role' => 'admin',
                'is_active' => true,
                'must_change_password' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('users')->insert([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'role' => 'admin',
                'is_active' => true,
                'must_change_password' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return [
            'name' => $name,
            'email' => $email,
            'role' => 'admin',
            'must_change_password' => true,
        ];
    }

    private function schemaNameFromSlug(string $slug): string
    {
        $prefix = (string) config('tenancy.database.tenant_schema_prefix', 'tenant_');

        return $prefix . str_replace('-', '_', $slug);
    }
}
