<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Support\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

final class TenantForgotPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'El campo email es obligatorio.',
            'email.email' => 'El formato del email no es valido.',
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
