<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CreateTenantUserRequest;
use App\Models\TenantUser;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class TenantUserController extends Controller
{
    public function index(): JsonResponse
    {
        $users = TenantUser::query()
            ->orderBy('id')
            ->get()
            ->map(fn (TenantUser $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'is_active' => $user->is_active,
                'must_change_password' => $user->must_change_password,
                'created_at' => $user->created_at?->toIso8601String(),
            ])
            ->all();

        return ApiResponse::success($users);
    }

    public function store(CreateTenantUserRequest $request): JsonResponse
    {
        $tempPassword = Str::random(12);

        $user = TenantUser::query()->create([
            'name' => $request->string('name'),
            'email' => $request->string('email'),
            'password' => Hash::make($tempPassword),
            'role' => $request->string('role', 'professional'),
            'is_active' => true,
            'must_change_password' => true,
        ]);

        return ApiResponse::success([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'is_active' => $user->is_active,
            'temp_password' => $tempPassword,
            'message' => 'Usuario creado. Comparte la contraseña temporal de forma segura.',
        ], status: 201);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        /** @var TenantUser $currentUser */
        $currentUser = $request->user();

        if ($currentUser->id === $id) {
            return ApiResponse::error(
                message: 'No puedes desactivar tu propia cuenta.',
                code: 'cannot_deactivate_self',
                status: 422,
            );
        }

        $user = TenantUser::query()->findOrFail($id);

        $user->update(['is_active' => false]);

        return ApiResponse::success([
            'message' => 'Usuario desactivado correctamente.',
        ]);
    }
}
