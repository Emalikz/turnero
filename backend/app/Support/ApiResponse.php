<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Http\JsonResponse;

final class ApiResponse
{
    public static function success(array $data, array $meta = [], int $status = 200): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'meta' => [
                'timestamp' => now()->toIso8601String(),
                ...$meta,
            ],
            'error' => null,
        ], $status);
    }

    public static function error(string $message, string $code, int $status, array $details = []): JsonResponse
    {
        return response()->json([
            'data' => null,
            'meta' => [
                'timestamp' => now()->toIso8601String(),
            ],
            'error' => [
                'message' => $message,
                'code' => $code,
                'details' => $details,
            ],
        ], $status);
    }
}
