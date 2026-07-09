<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Support\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

final class CreateTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'string', 'max:80', 'alpha_dash', Rule::unique('tenants', 'slug')],
            'primary_domain' => ['required', 'string', 'max:150', Rule::unique('tenants', 'primary_domain')],
            'settings' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El campo name es obligatorio.',
            'slug.required' => 'El campo slug es obligatorio.',
            'slug.unique' => 'El slug ya ha sido tomado.',
            'primary_domain.required' => 'El campo primary domain es obligatorio.',
            'primary_domain.unique' => 'El primary domain ya ha sido tomado.',
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
