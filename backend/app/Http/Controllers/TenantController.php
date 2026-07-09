<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CreateTenantRequest;
use App\Services\TenantProvisioningService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

final class TenantController extends Controller
{
    public function __construct(
        private readonly TenantProvisioningService $tenantProvisioningService,
    ) {}

    public function store(CreateTenantRequest $request): JsonResponse
    {
        $tenant = $this->tenantProvisioningService->create($request->validated());

        return ApiResponse::success([
            'id' => $tenant->id,
            'name' => $tenant->name,
            'slug' => $tenant->slug,
            'schema' => $tenant->schema,
            'primary_domain' => $tenant->primary_domain,
            'settings' => $tenant->settings,
        ], status: 201);
    }

    public function index(): JsonResponse
    {
        $tenants = 
            \App\Models\Tenant::query()
                ->orderBy('id')
                ->get()
                ->map(fn (\App\Models\Tenant $tenant): array => [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'slug' => $tenant->slug,
                    'schema' => $tenant->schema,
                    'primary_domain' => $tenant->primary_domain,
                    'settings' => $tenant->settings,
                    'created_at' => $tenant->created_at?->toIso8601String(),
                ])
                ->all();

        return ApiResponse::success($tenants);
    }
}
