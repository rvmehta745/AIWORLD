<?php

namespace App\Http\Requests\V1\ProductType;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductTypeRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $id = $this->route('id');
        
        return [
            'name' => 'required|string|max:255|unique:product_types,name,' . $id,
            'tag_line' => 'nullable|string|max:255',
            'configuration' => 'nullable|string',
            'sort_order' => 'nullable|integer',
            'status' => 'nullable|string|in:Active,InActive',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Product type name is required.',
            'name.unique' => 'Product type name already exists.',
            'name.max' => 'Product type name must not exceed 255 characters.',
            'tag_line.max' => 'Tag line must not exceed 255 characters.',
            'sort_order.integer' => 'Sort order must be an integer.',
            'status.in' => 'Status must be either Active or InActive.',
        ];
    }
}
