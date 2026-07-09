<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureTenantAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var \App\Models\TenantUser|null $tenantUser */
        $tenantUser = $request->attributes->get('tenant_user');

        if ($tenantUser === null) {
            return ApiResponse::error(
                message: 'No autenticado como usuario de tenant.',
                code: 'unauthenticated',
                status: Response::HTTP_UNAUTHORIZED,
            );
        }

        if ($tenantUser->role !== 'admin') {
            return ApiResponse::error(
                message: 'No tienes permisos de administrador en este tenant.',
                code: 'tenant_admin_required',
                status: Response::HTTP_FORBIDDEN,
            );
        }

        return $next($request);
    }
}
