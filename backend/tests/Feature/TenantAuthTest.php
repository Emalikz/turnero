<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\TenantUser;
use App\Models\User;
use App\Services\TenantProvisioningService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class TenantAuthTest extends TestCase
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
                $table->string('password_reset_token')->nullable();
                $table->timestamp('password_reset_expires_at')->nullable();
            });
        }
    }

    public function test_tenant_user_can_login_with_valid_credentials(): void
    {
        $tenant = $this->createTenantWithUser();

        $response = $this->postJson('/api/v1/auth/tenant/login', [
            'email' => 'user@tenant.test',
            'password' => 'secret123',
        ], [
            'X-Tenant' => $tenant->slug,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.token', fn (string $token) => str_contains($token, '|'))
            ->assertJsonPath('data.user.email', 'user@tenant.test')
            ->assertJsonPath('data.user.must_change_password', false)
            ->assertJsonPath('data.tenant.slug', $tenant->slug)
            ->assertJsonPath('error', null);
    }

    public function test_tenant_login_rejects_invalid_credentials(): void
    {
        $tenant = $this->createTenantWithUser();

        $response = $this->postJson('/api/v1/auth/tenant/login', [
            'email' => 'user@tenant.test',
            'password' => 'wrongpassword',
        ], [
            'X-Tenant' => $tenant->slug,
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'invalid_credentials');
    }

    public function test_tenant_login_rejects_inactive_user(): void
    {
        $tenant = $this->createTenantWithUser();
        TenantUser::where('email', 'user@tenant.test')->update(['is_active' => false]);

        $response = $this->postJson('/api/v1/auth/tenant/login', [
            'email' => 'user@tenant.test',
            'password' => 'secret123',
        ], [
            'X-Tenant' => $tenant->slug,
        ]);

        $response
            ->assertStatus(403)
            ->assertJsonPath('error.code', 'account_disabled');
    }

    public function test_tenant_user_can_change_password(): void
    {
        $tenant = $this->createTenantWithUser();
        $token = $this->loginTenantUser('user@tenant.test', 'secret123', $tenant->slug);

        $response = $this->postJson('/api/v1/tenant/change-password', [
            'current_password' => 'secret123',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123',
        ], [
            'Authorization' => "Bearer $token",
            'X-Tenant' => $tenant->slug,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.message', 'Password actualizado correctamente.');

        $user = TenantUser::where('email', 'user@tenant.test')->first();
        $this->assertTrue(Hash::check('newpassword123', $user->password));
        $this->assertFalse($user->must_change_password);
    }

    public function test_tenant_user_cannot_change_password_with_wrong_current(): void
    {
        $tenant = $this->createTenantWithUser();
        $token = $this->loginTenantUser('user@tenant.test', 'secret123', $tenant->slug);

        $response = $this->postJson('/api/v1/tenant/change-password', [
            'current_password' => 'wrongpassword',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123',
        ], [
            'Authorization' => "Bearer $token",
            'X-Tenant' => $tenant->slug,
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'invalid_current_password');
    }

    public function test_tenant_user_can_request_password_reset(): void
    {
        $tenant = $this->createTenantWithUser();

        $response = $this->postJson('/api/v1/auth/tenant/forgot-password', [
            'email' => 'user@tenant.test',
        ], [
            'X-Tenant' => $tenant->slug,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.message', 'Si el email existe, recibiras un enlace para restablecer tu password.');
    }

    public function test_forgot_password_returns_200_even_for_nonexistent_email(): void
    {
        $tenant = $this->createTenant();

        $response = $this->postJson('/api/v1/auth/tenant/forgot-password', [
            'email' => 'nonexistent@tenant.test',
        ], [
            'X-Tenant' => $tenant->slug,
        ]);

        $response->assertOk();
    }

    public function test_tenant_user_can_reset_password_with_valid_token(): void
    {
        $tenant = $this->createTenantWithUser();

        $user = TenantUser::where('email', 'user@tenant.test')->first();
        $token = Str::random(64);
        $user->update([
            'password_reset_token' => Hash::make($token),
            'password_reset_expires_at' => now()->addMinutes(60),
        ]);

        $response = $this->postJson('/api/v1/auth/tenant/reset-password', [
            'token' => $token,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ], [
            'X-Tenant' => $tenant->slug,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.message', 'Password restablecido correctamente.');

        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));
        $this->assertNull($user->password_reset_token);
        $this->assertNull($user->password_reset_expires_at);
    }

    public function test_tenant_user_cannot_reset_password_with_expired_token(): void
    {
        $tenant = $this->createTenantWithUser();

        $user = TenantUser::where('email', 'user@tenant.test')->first();
        $token = Str::random(64);
        $user->update([
            'password_reset_token' => Hash::make($token),
            'password_reset_expires_at' => now()->subMinutes(60),
        ]);

        $response = $this->postJson('/api/v1/auth/tenant/reset-password', [
            'token' => $token,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ], [
            'X-Tenant' => $tenant->slug,
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'token_expired');
    }

    public function test_tenant_user_cannot_reset_password_with_invalid_token(): void
    {
        $tenant = $this->createTenantWithUser();

        $response = $this->postJson('/api/v1/auth/tenant/reset-password', [
            'token' => 'invalid-token',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ], [
            'X-Tenant' => $tenant->slug,
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'token_invalid');
    }

    public function test_tenant_user_can_get_profile(): void
    {
        $tenant = $this->createTenantWithUser();
        $token = $this->loginTenantUser('user@tenant.test', 'secret123', $tenant->slug);

        $response = $this->getJson('/api/v1/tenant/me', [
            'Authorization' => "Bearer $token",
            'X-Tenant' => $tenant->slug,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.email', 'user@tenant.test')
            ->assertJsonPath('data.role', 'admin')
            ->assertJsonPath('error', null);
    }

    public function test_tenant_admin_can_create_user(): void
    {
        $tenant = $this->createTenantWithUser();
        $token = $this->loginTenantUser('user@tenant.test', 'secret123', $tenant->slug);

        $response = $this->postJson('/api/v1/tenant/users', [
            'name' => 'New User',
            'email' => 'new@tenant.test',
            'role' => 'professional',
        ], [
            'Authorization' => "Bearer $token",
            'X-Tenant' => $tenant->slug,
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.email', 'new@tenant.test')
            ->assertJsonPath('data.role', 'professional')
            ->assertJsonStructure(['data' => ['id', 'name', 'email', 'role', 'temp_password']]);

        $this->assertDatabaseHas('users', [
            'email' => 'new@tenant.test',
            'role' => 'professional',
        ]);
    }

    public function test_tenant_admin_cannot_deactivate_self(): void
    {
        $tenant = $this->createTenantWithUser();
        $token = $this->loginTenantUser('user@tenant.test', 'secret123', $tenant->slug);

        $user = TenantUser::where('email', 'user@tenant.test')->first();

        $response = $this->deleteJson("/api/v1/tenant/users/{$user->id}", [], [
            'Authorization' => "Bearer $token",
            'X-Tenant' => $tenant->slug,
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'cannot_deactivate_self');
    }

    public function test_tenant_admin_can_list_users(): void
    {
        $tenant = $this->createTenantWithUser();
        $token = $this->loginTenantUser('user@tenant.test', 'secret123', $tenant->slug);

        $response = $this->getJson('/api/v1/tenant/users', [
            'Authorization' => "Bearer $token",
            'X-Tenant' => $tenant->slug,
        ]);

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.email', 'user@tenant.test');
    }

    public function test_provisioning_creates_default_admin_user(): void
    {
        $service = new TenantProvisioningService();

        $result = $service->create([
            'name' => 'Test Tenant',
            'slug' => 'test-tenant',
            'primary_domain' => 'test-tenant.test',
        ]);

        $this->assertNotNull($result['admin_user']);
        $this->assertNotNull($result['temp_password']);
        $this->assertEquals('admin@turnero.com', $result['admin_user']['email']);
        $this->assertEquals('Admin', $result['admin_user']['name']);
        $this->assertEquals('admin', $result['admin_user']['role']);
        $this->assertTrue($result['admin_user']['must_change_password']);
    }

    public function test_unauthenticated_access_to_protected_routes(): void
    {
        $tenant = $this->createTenant();

        $this->getJson('/api/v1/tenant/me', [
            'X-Tenant' => $tenant->slug,
        ])->assertUnauthorized();

        $this->postJson('/api/v1/tenant/change-password', [
            'current_password' => 'test',
            'new_password' => 'test',
            'new_password_confirmation' => 'test',
        ], [
            'X-Tenant' => $tenant->slug,
        ])->assertUnauthorized();
    }

    private function createTenant(): Tenant
    {
        return Tenant::query()->create([
            'name' => 'Test Tenant',
            'slug' => 'test-tenant',
            'schema' => 'tenant_test_tenant',
            'primary_domain' => 'test-tenant.test',
        ]);
    }

    private function createTenantWithUser(): Tenant
    {
        $tenant = $this->createTenant();

        TenantUser::query()->create([
            'name' => 'Test User',
            'email' => 'user@tenant.test',
            'password' => Hash::make('secret123'),
            'role' => 'admin',
            'is_active' => true,
            'must_change_password' => false,
        ]);

        return $tenant;
    }

    private function loginTenantUser(string $email, string $password, string $slug): string
    {
        $response = $this->postJson('/api/v1/auth/tenant/login', [
            'email' => $email,
            'password' => $password,
        ], [
            'X-Tenant' => $slug,
        ]);

        return $response->json('data.token');
    }
}
