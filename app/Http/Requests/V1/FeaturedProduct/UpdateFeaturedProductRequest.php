<?php

namespace App\Http\Requests\V1\FeaturedProduct;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFeaturedProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'product_type_id' => 'required|integer|exists:product_types,id',
            'product_ids' => 'required|array|min:1|max:10',
            'product_ids.*' => 'required|integer|exists:products,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'featured_url' => 'nullable|url|max:2048',
            'sort_order' => 'nullable|integer|min:0',
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'product_type_id.required' => 'Product type is required.',
            'product_type_id.exists' => 'Selected product type does not exist.',
            'product_ids.required' => 'At least one product must be selected.',
            'product_ids.array' => 'Product IDs must be an array.',
            'product_ids.min' => 'At least one product must be selected.',
            'product_ids.max' => 'Maximum 10 products can be selected.',
            'product_ids.*.exists' => 'One or more selected products do not exist.',
            'start_date.required' => 'Start date is required.',
            'end_date.required' => 'End date is required.',
            'end_date.after' => 'End date must be after start date.',
            'featured_url.url' => 'Featured URL must be a valid URL.',
            'featured_url.max' => 'Featured URL must not exceed 2048 characters.',
            'sort_order.integer' => 'Sort order must be an integer.',
            'sort_order.min' => 'Sort order must be greater than or equal to 0.',
        ];
    }
} 