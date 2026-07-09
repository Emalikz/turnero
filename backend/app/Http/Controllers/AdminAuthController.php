<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\AdminLoginRequest;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

final class AdminAuthController extends Controller
{
    public function store(AdminLoginRequest $request): JsonResponse
    {
        $user = User::query()->where('email', $request->string('email'))->first();

        if ($user === null || ! Hash::check($request->string('password')->toString(), $user->password)) {
            return ApiResponse::error(
                message: 'Credenciales invalidas.',
                code: 'invalid_credentials',
                status: 422,
            );
        }

        if (! $user->is_platform_admin) {
            return ApiResponse::error(
                message: 'No tienes acceso al modulo administrativo.',
                code: 'admin_access_denied',
                status: 403,
            );
        }

        $token = $user->createToken('admin-panel')->plainTextToken;

        return ApiResponse::success([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_platform_admin' => $user->is_platform_admin,
            ],
        ]);
    }
}
