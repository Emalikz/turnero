<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_platform_admin_can_log_in_and_receive_a_token(): void
    {
        User::query()->create([
            'name' => 'Platform Admin',
            'email' => 'admin@turnero.test',
            'password' => Hash::make('secret123'),
            'is_platform_admin' => true,
        ]);

        $response = $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'admin@turnero.test',
            'password' => 'secret123',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.user.email', 'admin@turnero.test')
            ->assertJsonPath('data.user.is_platform_admin', true)
            ->assertJsonPath('error', null);

        $this->assertNotEmpty($response->json('data.token'));
    }

    public function test_non_platform_user_cannot_log_in_to_admin_module(): void
    {
        User::query()->create([
            'name' => 'Regular User',
            'email' => 'user@turnero.test',
            'password' => Hash::make('secret123'),
            'is_platform_admin' => false,
        ]);

        $response = $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'user@turnero.test',
            'password' => 'secret123',
        ]);

        $response
            ->assertForbidden()
            ->assertJsonPath('data', null)
            ->assertJsonPath('error.code', 'admin_access_denied');
    }
}
