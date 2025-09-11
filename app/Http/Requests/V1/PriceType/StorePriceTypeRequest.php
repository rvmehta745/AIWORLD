<?php

namespace App\Http\Requests\V1\PriceType;

use Illuminate\Foundation\Http\FormRequest;

class StorePriceTypeRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'product_type_id' => 'required|integer|exists:product_types,id',
            'name' => 'required|string|max:255|unique:price_types,name',
            'status' => 'nullable|string|in:Active,InActive',
        ];
    }

    public function messages()
    {
        return [
            'product_type_id.required' => 'Product type is required.',
            'product_type_id.exists' => 'Selected product type does not exist.',
            'name.required' => 'Price type name is required.',
            'name.unique' => 'Price type name already exists.',
            'name.max' => 'Price type name must not exceed 255 characters.',
            'status.in' => 'Status must be either Active or InActive.',
        ];
    }
}
