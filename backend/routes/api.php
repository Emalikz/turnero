<?php

declare(strict_types=1);

use App\Events\PublicDisplayUpdated;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\TenantController;
use App\Support\ApiResponse;
use App\Support\CurrentTenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/admin')->group(function (): void {
    Route::post('/auth/login', [AdminAuthController::class, 'store']);

    Route::middleware(['auth:sanctum', 'platform.admin'])->group(function (): void {
        Route::get('/tenants', [TenantController::class, 'index']);
        Route::post('/tenants', [TenantController::class, 'store']);
    });
});

Route::prefix('v1')->middleware('tenant')->group(function (): void {

    Route::get('/health', function (CurrentTenant $currentTenant) {
        $tenant = $currentTenant->get();

        return ApiResponse::success([
            'status' => 'ok',
            'app' => config('app.name'),
            'tenant' => $tenant === null ? null : [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
                'schema' => $tenant->schema,
            ],
        ]);
    });

    Route::post('/display/demo-call', function (Request $request, CurrentTenant $currentTenant) {
        $payload = $request->validate([
            'turn_code' => ['required', 'string', 'max:20'],
            'desk' => ['required', 'string', 'max:100'],
            'message' => ['nullable', 'string', 'max:255'],
        ]);

        $tenant = $currentTenant->get();
        $tenantSlug = $tenant?->slug ?? 'public';

        event(new PublicDisplayUpdated(
            tenantSlug: $tenantSlug,
            turnCode: $payload['turn_code'],
            desk: $payload['desk'],
            message: $payload['message'] ?? null,
        ));

        return ApiResponse::success([
            'tenant_slug' => $tenantSlug,
            'channel' => sprintf('public-display.%s', $tenantSlug),
            'turn_code' => $payload['turn_code'],
            'desk' => $payload['desk'],
            'message' => $payload['message'] ?? null,
        ]);
    });
});
