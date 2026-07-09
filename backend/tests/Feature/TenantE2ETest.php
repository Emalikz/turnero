<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Tenant;
use App\Services\TenantProvisioningService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class TenantE2ETest extends TestCase
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

    public function test_full_flow_create_tenant_login_change_password_dashboard(): void
    {
        $service = new TenantProvisioningService();

        // 1. Create tenant
        $result = $service->create([
            'name' => 'Flow Test',
            'slug' => 'flow-test',
            'primary_domain' => 'flow-test.test',
        ]);

        $tenant = $result['tenant'];
        $tempPassword = $result['temp_password'];

        $this->assertInstanceOf(Tenant::class, $tenant);
        $this->assertNotNull($tempPassword);
        $this->assertEquals('admin@turnero.com', $result['admin_user']['email']);
        $this->assertTrue($result['admin_user']['must_change_password']);

        // 2. Login with temp password
        $loginResponse = $this->postJson('/api/v1/auth/tenant/login', [
            'email' => 'admin@turnero.com',
            'password' => $tempPassword,
        ], [
            'X-Tenant' => $tenant->slug,
        ]);

        $loginResponse->assertOk();

        $token = $loginResponse->json('data.token');
        $this->assertNotEmpty($token);

        // 3. Change password
        $changeResponse = $this->postJson('/api/v1/tenant/change-password', [
            'current_password' => $tempPassword,
            'new_password' => 'MyNewPass123!',
            'new_password_confirmation' => 'MyNewPass123!',
        ], [
            'Authorization' => "Bearer $token",
            'X-Tenant' => $tenant->slug,
        ]);

        $changeResponse->assertOk()
            ->assertJsonPath('data.message', 'Password actualizado correctamente.');

        // 4. Access dashboard (GET /me)
        $meResponse = $this->getJson('/api/v1/tenant/me', [
            'Authorization' => "Bearer $token",
            'X-Tenant' => $tenant->slug,
        ]);

        $meResponse->assertOk()
            ->assertJsonPath('data.email', 'admin@turnero.com')
            ->assertJsonPath('data.role', 'admin')
            ->assertJsonPath('data.must_change_password', false);
    }
}
