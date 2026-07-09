<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\TenantUser;
use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureTenantUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return ApiResponse::error(
                message: 'No autenticado.',
                code: 'unauthenticated',
                status: Response::HTTP_UNAUTHORIZED,
            );
        }

        $tenantUser = TenantUser::query()
            ->where('id', $user->id)
            ->where('is_active', true)
            ->first();

        if ($tenantUser === null) {
            return ApiResponse::error(
                message: 'Tu cuenta no fue encontrada o ha sido desactivada.',
                code: 'tenant_user_not_found',
                status: Response::HTTP_FORBIDDEN,
            );
        }

        $request->attributes->set('tenant_user', $tenantUser);

        return $next($request);
    }
}
