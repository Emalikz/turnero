<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Support\ApiResponse;
use App\Support\CurrentTenant;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

final class ResolveTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $identifier = $request->header('X-Tenant');

        if ($identifier === null || $identifier === '') {
            return $next($request);
        }

        $tenant = Tenant::query()
            ->where('slug', $identifier)
            ->orWhere('primary_domain', $identifier)
            ->first();

        if ($tenant === null) {
            return ApiResponse::error(
                message: 'Tenant no encontrado.',
                code: 'tenant_not_found',
                status: Response::HTTP_NOT_FOUND,
            );
        }

        $currentTenant = new CurrentTenant();
        $currentTenant->set($tenant);

        app()->instance(CurrentTenant::class, $currentTenant);
        $request->attributes->set('tenant', $tenant);

        if (DB::getDriverName() === 'pgsql') {
            DB::statement(sprintf('set search_path to "%s", public', str_replace('"', '', $tenant->schema)));
        }

        return $next($request);
    }
}
