<?php

namespace App\Http\Requests\V1\Product;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');
        return [
            // Basic Information
            'product_type_id' => 'required|exists:product_types,id',
            'name' => 'required|string|min:3|max:100|unique:products,name,' . $id . ',id,deleted_at,NULL',
            'short_description' => 'required|string|min:10|max:150',
            'long_description' => 'nullable|string|min:20|max:2000',
            
            // Media Information
            'logo_image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048', // 2MB max
            'product_image' => 'nullable|array', // Multiple images as JSON array
            'product_image.*' => 'image|mimes:jpeg,jpg,png,webp|max:5120', // 5MB max per image
            'video_url' => 'nullable|url|max:300',
            
            // Links & Social Media
            'product_url' => 'required|url|max:300',
            'extra_link1' => 'nullable|url|max:300',
            'extra_link2' => 'nullable|url|max:300',
            'extra_link3' => 'nullable|url|max:300',
            'twitter' => 'nullable|url|max:200',
            'facebook' => 'nullable|url|max:200',
            'linkedin' => 'nullable|url|max:200',
            'telegram' => 'nullable|url|max:200',
            
            // Categorization
            'category_ids' => 'required|array|min:1|max:5',
            'category_ids.*' => 'exists:categories,id',
            'price_type_ids' => 'required|array|min:1|max:2',
            'price_type_ids.*' => 'exists:price_types,id',
            
            // Status & Badges
            'status' => 'required|in:Active,Inactive,Draft',
            'payment_status' => 'nullable|in:Paid,Unpaid',
            'is_verified' => 'nullable|boolean',
            'is_gold' => 'nullable|boolean',
            
            // SEO & Additional Info
            'seo_text' => 'nullable|string|min:10|max:160',
            'additional_info' => 'nullable|string|max:500',
            
            // Use cases (optional)
            'use_case1' => 'nullable|string',
            'use_case2' => 'nullable|string',
            'use_case3' => 'nullable|string',
            'use_cases' => 'nullable|array|max:20',
            'use_cases.*' => 'string|max:1000',
            'features_and_highlights' => 'nullable|array|max:20',
            'features_and_highlights.*' => 'string|max:1000',
            
            // Other fields
            'published_at' => 'nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
            'name.min' => 'Product name must be at least 3 characters.',
            'name.max' => 'Product name cannot exceed 100 characters.',
            'short_description.min' => 'Short description must be at least 10 characters.',
            'short_description.max' => 'Short description cannot exceed 150 characters.',
            'long_description.min' => 'Long description must be at least 20 characters.',
            'long_description.max' => 'Long description cannot exceed 2000 characters.',
            'category_ids.min' => 'At least 1 category must be selected.',
            'category_ids.max' => 'Maximum 5 categories can be selected.',
            'price_type_ids.min' => 'At least 1 price type must be selected.',
            'price_type_ids.max' => 'Maximum 2 price types can be selected.',
            'product_url.required' => 'Product URL is required.',
            'status.required' => 'Product status is required.',
            'use_cases.max' => 'Use cases cannot exceed 20 items.',
            'features_and_highlights.max' => 'Features and highlights cannot exceed 20 items.',
        ];
    }
}
