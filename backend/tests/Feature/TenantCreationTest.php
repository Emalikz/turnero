<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class TenantCreationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->string('role')->default('admin');
                $table->boolean('is_active')->default(true);
                $table->boolean('must_change_password')->default(false);
            });
        }
    }

    public function test_platform_admin_can_create_a_tenant_and_derive_its_schema(): void
    {
        Sanctum::actingAs($this->platformAdmin());

        $response = $this->postJson('/api/v1/admin/tenants', [
            'name' => 'Acme Salud',
            'slug' => 'acme-salud',
            'primary_domain' => 'acme.test',
            'settings' => [
                'timezone' => 'America/Bogota',
            ],
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.name', 'Acme Salud')
            ->assertJsonPath('data.slug', 'acme-salud')
            ->assertJsonPath('data.primary_domain', 'acme.test')
            ->assertJsonPath('data.schema', 'tenant_acme_salud')
            ->assertJsonPath('data.settings.timezone', 'America/Bogota')
            ->assertJsonPath('data.admin_user.email', 'admin@turnero.com')
            ->assertJsonPath('data.admin_user.role', 'admin')
            ->assertJsonPath('error', null);

        $this->assertDatabaseHas('tenants', [
            'name' => 'Acme Salud',
            'slug' => 'acme-salud',
            'primary_domain' => 'acme.test',
            'schema' => 'tenant_acme_salud',
        ]);
    }

    public function test_tenant_creation_requires_authenticated_admin(): void
    {
        $response = $this->postJson('/api/v1/admin/tenants', [
            'name' => 'Acme Salud',
            'slug' => 'acme-salud',
            'primary_domain' => 'acme.test',
        ]);

        $response->assertUnauthorized();
    }

    public function test_non_platform_user_cannot_create_a_tenant(): void
    {
        Sanctum::actingAs(User::query()->create([
            'name' => 'Regular User',
            'email' => 'user@turnero.test',
            'password' => Hash::make('secret123'),
            'is_platform_admin' => false,
        ]));

        $response = $this->postJson('/api/v1/admin/tenants', [
            'name' => 'Acme Salud',
            'slug' => 'acme-salud',
            'primary_domain' => 'acme.test',
        ]);

        $response
            ->assertForbidden()
            ->assertJsonPath('data', null)
            ->assertJsonPath('error.code', 'admin_access_denied');
    }

    public function test_it_rejects_duplicate_tenant_slug(): void
    {
        Sanctum::actingAs($this->platformAdmin());

        Tenant::query()->create([
            'name' => 'Acme Salud',
            'slug' => 'acme-salud',
            'schema' => 'tenant_acme_salud',
            'primary_domain' => 'acme.test',
        ]);

        $response = $this->postJson('/api/v1/admin/tenants', [
            'name' => 'Acme Salud 2',
            'slug' => 'acme-salud',
            'primary_domain' => 'acme-2.test',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonPath('data', null)
            ->assertJsonPath('error.code', 'validation_error')
            ->assertJsonPath('error.details.slug.0', 'El slug ya ha sido tomado.');
    }

    public function test_it_requires_the_minimum_fields_to_create_a_tenant(): void
    {
        Sanctum::actingAs($this->platformAdmin());

        $response = $this->postJson('/api/v1/admin/tenants', []);

        $response
            ->assertStatus(422)
            ->assertJsonPath('data', null)
            ->assertJsonPath('error.code', 'validation_error')
            ->assertJsonPath('error.details.name.0', 'El campo name es obligatorio.')
            ->assertJsonPath('error.details.slug.0', 'El campo slug es obligatorio.')
            ->assertJsonPath('error.details.primary_domain.0', 'El campo primary domain es obligatorio.');
    }

    public function test_platform_admin_can_list_tenants(): void
    {
        Sanctum::actingAs($this->platformAdmin());

        Tenant::query()->create([
            'name' => 'Acme Salud',
            'slug' => 'acme-salud',
            'schema' => 'tenant_acme_salud',
            'primary_domain' => 'acme.test',
        ]);

        Tenant::query()->create([
            'name' => 'Beta Clinic',
            'slug' => 'beta-clinic',
            'schema' => 'tenant_beta_clinic',
            'primary_domain' => 'beta.test',
        ]);

        $response = $this->getJson('/api/v1/admin/tenants');

        $response
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('error', null);

        $slugs = collect($response->json('data'))->pluck('slug')->values()->all();
        $this->assertContains('acme-salud', $slugs);
        $this->assertContains('beta-clinic', $slugs);
    }

    private function platformAdmin(): User
    {
        return User::query()->create([
            'name' => 'Platform Admin',
            'email' => 'admin@turnero.test',
            'password' => Hash::make('secret123'),
            'is_platform_admin' => true,
        ]);
    }
}
