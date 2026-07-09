<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsurePlatformAdmin
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

        if (! $user->is_platform_admin) {
            return ApiResponse::error(
                message: 'No tienes acceso al modulo administrativo.',
                code: 'admin_access_denied',
                status: Response::HTTP_FORBIDDEN,
            );
        }

        return $next($request);
    }
}
