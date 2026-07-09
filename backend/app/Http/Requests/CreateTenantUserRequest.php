<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Support\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

final class CreateTenantUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'role' => ['nullable', 'string', Rule::in(['admin', 'professional'])],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El campo nombre es obligatorio.',
            'email.required' => 'El campo email es obligatorio.',
            'email.email' => 'El formato del email no es valido.',
            'email.unique' => 'Este email ya esta registrado en este tenant.',
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
