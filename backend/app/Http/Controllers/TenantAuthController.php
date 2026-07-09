<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\TenantChangePasswordRequest;
use App\Http\Requests\TenantForgotPasswordRequest;
use App\Http\Requests\TenantLoginRequest;
use App\Mail\TenantPasswordResetMail;
use App\Models\TenantUser;
use App\Support\ApiResponse;
use App\Support\CurrentTenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

final class TenantAuthController extends Controller
{
    public function store(TenantLoginRequest $request, CurrentTenant $currentTenant): JsonResponse
    {
        $tenant = $currentTenant->get();

        if ($tenant === null) {
            return ApiResponse::error(
                message: 'Tenant no resuelto.',
                code: 'tenant_not_resolved',
                status: 422,
            );
        }

        $user = TenantUser::query()
            ->where('email', $request->string('email'))
            ->first();

        if ($user === null || ! Hash::check($request->string('password')->toString(), $user->password)) {
            return ApiResponse::error(
                message: 'Credenciales invalidas.',
                code: 'invalid_credentials',
                status: 422,
            );
        }

        if (! $user->is_active) {
            return ApiResponse::error(
                message: 'Tu cuenta ha sido desactivada. Contacta al administrador.',
                code: 'account_disabled',
                status: 403,
            );
        }

        $token = $user->createToken('tenant-user')->plainTextToken;

        return ApiResponse::success([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'is_active' => $user->is_active,
                'must_change_password' => $user->must_change_password,
            ],
            'tenant' => [
                'slug' => $tenant->slug,
                'name' => $tenant->name,
            ],
        ]);
    }

    public function update(TenantChangePasswordRequest $request): JsonResponse
    {
        /** @var TenantUser $user */
        $user = $request->user();

        if (! Hash::check($request->string('current_password')->toString(), $user->password)) {
            return ApiResponse::error(
                message: 'El password actual es incorrecto.',
                code: 'invalid_current_password',
                status: 422,
            );
        }

        $user->forceFill([
            'password' => Hash::make($request->string('new_password')->toString()),
            'must_change_password' => false,
        ])->save();

        return ApiResponse::success([
            'message' => 'Password actualizado correctamente.',
        ]);
    }

    public function forgotPassword(TenantForgotPasswordRequest $request): JsonResponse
    {
        $email = $request->string('email')->toString();

        $user = TenantUser::query()
            ->where('email', $email)
            ->first();

        if ($user !== null) {
            $token = Str::random(64);

            $user->forceFill([
                'password_reset_token' => Hash::make($token),
                'password_reset_expires_at' => now()->addMinutes(60),
            ])->save();

            $tenant = $request->attributes->get('tenant');

            Mail::to($user->email)->send(
                new TenantPasswordResetMail(
                    userName: $user->name,
                    token: $token,
                    tenantSlug: $tenant?->slug ?? '',
                ),
            );
        }

        return ApiResponse::success([
            'message' => 'Si el email existe, recibiras un enlace para restablecer tu password.',
        ]);
    }

    public function resetPassword(\App\Http\Requests\TenantResetPasswordRequest $request): JsonResponse
    {
        $token = $request->string('token')->toString();

        $user = TenantUser::query()
            ->where('password_reset_token', '!=', null)
            ->first();

        if ($user === null || ! Hash::check($token, $user->password_reset_token)) {
            return ApiResponse::error(
                message: 'El token es invalido.',
                code: 'token_invalid',
                status: 422,
            );
        }

        if ($user->password_reset_expires_at === null || $user->password_reset_expires_at->isPast()) {
            return ApiResponse::error(
                message: 'El token ha expirado.',
                code: 'token_expired',
                status: 422,
            );
        }

        $user->forceFill([
            'password' => Hash::make($request->string('password')->toString()),
            'password_reset_token' => null,
            'password_reset_expires_at' => null,
            'must_change_password' => false,
        ])->save();

        return ApiResponse::success([
            'message' => 'Password restablecido correctamente.',
        ]);
    }

    public function show(\Illuminate\Http\Request $request): JsonResponse
    {
        /** @var TenantUser $user */
        $user = $request->user();

        return ApiResponse::success([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'is_active' => $user->is_active,
            'must_change_password' => $user->must_change_password,
            'created_at' => $user->created_at?->toIso8601String(),
        ]);
    }
}
