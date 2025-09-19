<?php

namespace App\Http\Requests\V1\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_type_id' => 'required|exists:product_types,id',
            'name' => 'required|string|max:255|unique:products,name,NULL,id,deleted_at,NULL',
            'short_description' => 'nullable|string',
            'long_description' => 'nullable|string',
            'product_url' => 'nullable|url|max:2048',
            'video_url' => 'nullable|url|max:2048',
            'seo_text' => 'nullable|string',
            'extra_link1' => 'nullable|url|max:2048',
            'extra_link2' => 'nullable|url|max:2048',
            'extra_link3' => 'nullable|url|max:2048',
            'use_case1' => 'nullable|string',
            'use_case2' => 'nullable|string',
            'use_case3' => 'nullable|string',
            'additional_info' => 'nullable|string|max:255',
            'twitter' => 'nullable|string|max:255',
            'facebook' => 'nullable|string|max:255',
            'linkedin' => 'nullable|string|max:255',
            'telegram' => 'nullable|string|max:255',
            'published_at' => 'nullable|date',
            'payment_status' => 'nullable|in:Pending,Success,Failed,ReadyForRefund',
            'status' => 'nullable|in:Pending,OneTimeLinkPending,OneTimeLinkUsed',
            'is_verified' => 'nullable|boolean',
            'is_gold' => 'nullable|boolean',
            'is_human_verified' => 'nullable|boolean',
            'one_time_token' => 'nullable|string|max:255|unique:products,one_time_token,NULL,id,deleted_at,NULL',
            'is_token_used' => 'nullable|boolean',
            'logo_image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:4096',
            'product_image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:4096',
        ];
    }
}
