<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Store Product Request.
 *
 * @requirement PROD-006 Create product form with validation
 * @requirement PROD-016 Product slug generation
 */
class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @requirement PROD-006 Admin and Support roles can create products
     */
    public function authorize(): bool
    {
        return $this->user() && in_array($this->user()->role, [User::ROLE_ADMIN, User::ROLE_STAFF], true);
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Auto-generate slug if not provided
        if (empty($this->slug) && !empty($this->name)) {
            $this->merge([
                'slug' => Str::slug($this->name),
            ]);
        }

        // Auto-generate SKU if not provided
        if (empty($this->sku) && !empty($this->name)) {
            $prefix = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $this->name), 0, 3));
            $random = strtoupper(Str::random(6));
            $this->merge([
                'sku' => "SKU-{$prefix}{$random}",
            ]);
        }

        // Set default values
        $this->merge([
            'is_active' => $this->is_active ?? true,
            'is_featured' => $this->is_featured ?? false,
            'unit' => $this->unit ?? 'kg',
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:products,slug', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'description' => ['nullable', 'string', 'max:5000'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'price_aud' => ['required', 'numeric', 'min:0.01', 'max:99999.99'],
            'sale_price_aud' => ['nullable', 'numeric', 'min:0', 'max:99999.99', 'lt:price_aud'],
            'stock' => ['required', 'integer', 'min:0', 'max:999999'],
            'sku' => ['required', 'string', 'max:100', 'unique:products,sku'],
            'unit' => ['required', 'string', Rule::in(['kg', 'piece', 'pack'])],
            'weight_kg' => ['nullable', 'numeric', 'min:0.001', 'max:999.999'],
            'is_active' => ['boolean'],
            'is_featured' => ['boolean'],
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
            'name.required' => 'Product name is required.',
            'name.min' => 'Product name must be at least 3 characters.',
            'slug.unique' => 'This product slug is already taken.',
            'slug.regex' => 'Product slug must be lowercase with hyphens only.',
            'category_id.required' => 'Please select a category.',
            'category_id.exists' => 'Selected category does not exist.',
            'price_aud.required' => 'Price is required.',
            'price_aud.min' => 'Price must be at least $0.01.',
            'sale_price_aud.lt' => 'Sale price must be less than regular price.',
            'stock.required' => 'Stock quantity is required.',
            'stock.min' => 'Stock cannot be negative.',
            'sku.required' => 'SKU is required.',
            'sku.unique' => 'This SKU is already in use.',
            'unit.in' => 'Unit must be kg, piece, or pack.',
            'images.max' => 'Maximum 5 images allowed.',
            'images.*.image' => 'Each file must be a valid image.',
            'images.*.mimes' => 'Images must be JPEG, JPG, PNG, or WEBP format.',
            'images.*.max' => 'Each image must not exceed 2MB.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'price_aud' => 'price',
            'sale_price_aud' => 'sale price',
            'category_id' => 'category',
            'is_active' => 'active status',
            'is_featured' => 'featured status',
        ];
    }
}
