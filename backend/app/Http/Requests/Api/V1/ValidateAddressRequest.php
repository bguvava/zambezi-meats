<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validate Address Request
 *
 * @requirement CHK-006 Validate delivery zone
 */
class ValidateAddressRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Public endpoint
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'street_address' => ['sometimes', 'string', 'max:255'],
            'suburb' => ['sometimes', 'string', 'max:100'],
            'state' => ['sometimes', 'string', 'max:50'],
            'postcode' => ['required', 'string', 'regex:/^\d{4}$/'],
            'country' => ['sometimes', 'string', 'max:100'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'postcode.required' => 'Please enter your postcode.',
            'postcode.regex' => 'Please enter a valid 4-digit Australian postcode.',
        ];
    }
}
