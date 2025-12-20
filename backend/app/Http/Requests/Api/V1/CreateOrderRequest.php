<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Create Order Request
 *
 * @requirement CHK-025 Create order in database
 */
class CreateOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Address - either saved address ID or new address details
            'address_id' => ['sometimes', 'integer', 'exists:addresses,id'],
            'address_label' => ['sometimes', 'string', 'max:50'],
            'street' => ['required_without:address_id', 'string', 'max:255'],
            'suburb' => ['sometimes', 'nullable', 'string', 'max:100'],
            'city' => ['required_without:address_id', 'string', 'max:100'],
            'state' => ['required_without:address_id', 'string', 'max:50'],
            'postcode' => ['required_without:address_id', 'string', 'regex:/^\d{4}$/'],
            'save_address' => ['sometimes', 'boolean'],

            // Payment
            'payment_method' => ['required', 'string', 'in:stripe,paypal,afterpay,cod'],

            // Optional fields
            'promo_code' => ['sometimes', 'nullable', 'string', 'max:50'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'delivery_instructions' => ['sometimes', 'nullable', 'string', 'max:500'],
            'scheduled_date' => ['sometimes', 'nullable', 'date', 'after_or_equal:today'],
            'scheduled_time_slot' => ['sometimes', 'nullable', 'string', 'max:50'],
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
            'street.required_without' => 'Please provide a delivery address.',
            'city.required_without' => 'Please provide your city.',
            'state.required_without' => 'Please provide your state.',
            'postcode.required_without' => 'Please provide your postcode.',
            'postcode.regex' => 'Please enter a valid 4-digit Australian postcode.',
            'payment_method.required' => 'Please select a payment method.',
            'payment_method.in' => 'Invalid payment method selected.',
            'address_id.exists' => 'Selected address not found.',
            'scheduled_date.after_or_equal' => 'Scheduled delivery date must be today or later.',
        ];
    }
}
