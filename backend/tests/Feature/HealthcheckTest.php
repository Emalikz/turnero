<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Events\PublicDisplayUpdated;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

final class HealthcheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_healthcheck_returns_the_standard_api_envelope(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response
            ->assertOk()
            ->assertJsonPath('data.status', 'ok')
            ->assertJsonPath('data.tenant', null)
            ->assertJsonPath('error', null)
            ->assertJsonStructure([
                'data' => ['status', 'app', 'tenant'],
                'meta' => ['timestamp'],
                'error',
            ]);
    }

    public function test_healthcheck_resolves_tenant_context_from_header(): void
    {
        Tenant::create([
            'name' => 'Acme Salud',
            'slug' => 'acme',
            'schema' => 'tenant_acme',
            'primary_domain' => 'acme.test',
        ]);

        $response = $this
            ->withHeader('X-Tenant', 'acme')
            ->getJson('/api/v1/health');

        $response
            ->assertOk()
            ->assertJsonPath('data.tenant.slug', 'acme')
            ->assertJsonPath('data.tenant.schema', 'tenant_acme')
            ->assertJsonPath('error', null);
    }

    public function test_unknown_tenant_returns_consistent_not_found_envelope(): void
    {
        $response = $this
            ->withHeader('X-Tenant', 'missing-tenant')
            ->getJson('/api/v1/health');

        $response
            ->assertNotFound()
            ->assertJsonPath('data', null)
            ->assertJsonPath('error.code', 'tenant_not_found');
    }

    public function test_demo_display_call_dispatches_a_broadcast_event(): void
    {
        Tenant::create([
            'name' => 'Acme Salud',
            'slug' => 'acme',
            'schema' => 'tenant_acme',
            'primary_domain' => 'acme.test',
        ]);

        Event::fake();

        $response = $this
            ->withHeader('X-Tenant', 'acme')
            ->postJson('/api/v1/display/demo-call', [
                'turn_code' => 'A-101',
                'desk' => 'Modulo 4',
                'message' => 'Paciente pasar a ventanilla.',
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.turn_code', 'A-101')
            ->assertJsonPath('data.channel', 'public-display.acme')
            ->assertJsonPath('error', null);

        Event::assertDispatched(PublicDisplayUpdated::class, function (PublicDisplayUpdated $event): bool {
            return $event->turnCode === 'A-101'
                && $event->desk === 'Modulo 4'
                && $event->message === 'Paciente pasar a ventanilla.'
                && $event->tenantSlug === 'acme';
        });
    }
}
