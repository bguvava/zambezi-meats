<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Update Product Request.
 *
 * @requirement PROD-008 Update product details with validation
 */
class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && in_array($this->user()->role, [User::ROLE_ADMIN, User::ROLE_STAFF], true);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $productId = $this->route('id');

        return [
            'name' => ['sometimes', 'string', 'min:3', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255', Rule::unique('products', 'slug')->ignore($productId), 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'description' => ['nullable', 'string', 'max:5000'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'category_id' => ['sometimes', 'integer', 'exists:categories,id'],
            'price_aud' => ['sometimes', 'numeric', 'min:0.01', 'max:99999.99'],
            'sale_price_aud' => ['nullable', 'numeric', 'min:0', 'max:99999.99'],
            'stock' => ['sometimes', 'integer', 'min:0', 'max:999999'],
            'sku' => ['sometimes', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($productId)],
            'unit' => ['sometimes', 'string', Rule::in(['kg', 'piece', 'pack'])],
            'weight_kg' => ['nullable', 'numeric', 'min:0.001', 'max:999.999'],
            'is_active' => ['sometimes', 'boolean'],
            'is_featured' => ['sometimes', 'boolean'],
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => ['image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
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
            'name.min' => 'Product name must be at least 3 characters.',
            'slug.unique' => 'This product slug is already taken.',
            'slug.regex' => 'Product slug must be lowercase with hyphens only.',
            'category_id.exists' => 'Selected category does not exist.',
            'price_aud.min' => 'Price must be at least $0.01.',
            'stock.min' => 'Stock cannot be negative.',
            'sku.unique' => 'This SKU is already in use.',
            'unit.in' => 'Unit must be kg, piece, or pack.',
            'images.max' => 'Maximum 5 images allowed.',
            'images.*.image' => 'Each file must be a valid image.',
            'images.*.mimes' => 'Images must be JPEG, JPG, PNG, or WEBP format.',
            'images.*.max' => 'Each image must not exceed 2MB.',
        ];
    }

    /**
     * Validate sale price is less than regular price.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->filled('sale_price_aud') && $this->filled('price_aud')) {
                if ($this->sale_price_aud >= $this->price_aud) {
                    $validator->errors()->add('sale_price_aud', 'Sale price must be less than regular price.');
                }
            }
        });
    }
}
