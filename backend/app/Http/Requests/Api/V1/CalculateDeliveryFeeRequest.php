<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Calculate Delivery Fee Request
 *
 * @requirement CHK-007 Calculate delivery fee
 */
class CalculateDeliveryFeeRequest extends FormRequest
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
            'suburb' => ['sometimes', 'string', 'max:100'],
            'postcode' => ['required', 'string', 'regex:/^\d{4}$/'],
            'subtotal' => ['required', 'numeric', 'min:0'],
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
            'subtotal.required' => 'Cart subtotal is required.',
            'subtotal.numeric' => 'Cart subtotal must be a number.',
        ];
    }
}
