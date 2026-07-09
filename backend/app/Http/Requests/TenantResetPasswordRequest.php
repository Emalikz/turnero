<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Support\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

final class TenantResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'max:255', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'token.required' => 'El campo token es obligatorio.',
            'password.required' => 'El campo password es obligatorio.',
            'password.min' => 'El password debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmacion del password no coincide.',
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
