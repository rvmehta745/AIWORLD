<?php

namespace App\Http\Requests\V1\Category;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');
        return [
            'product_type_id' => 'required|exists:product_types,id',
            'parent_id' => 'nullable|exists:categories,id',
            'name' => 'required|string|max:255|unique:categories,name,' . $id . ',id,deleted_at,NULL',
            'logo' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
            'description' => 'nullable|string',
            'tools_count' => 'nullable|integer|min:0',
            'sort_order' => 'nullable|integer',
            'status' => 'nullable|in:Active,InActive',
        ];
    }
} 