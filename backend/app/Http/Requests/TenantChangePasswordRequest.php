<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Support\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

final class TenantChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string', 'max:255'],
            'new_password' => ['required', 'string', 'min:8', 'max:255', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'El campo password actual es obligatorio.',
            'new_password.required' => 'El campo nuevo password es obligatorio.',
            'new_password.min' => 'El nuevo password debe tener al menos 8 caracteres.',
            'new_password.confirmed' => 'La confirmacion del password no coincide.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            ApiResponse::error(
                message: 'La peticion no paso validacion.',
                code: 'validation_error',
                status: 422,
                details: $validator->errors()->toArray(),
            ),
        );
    }
}
