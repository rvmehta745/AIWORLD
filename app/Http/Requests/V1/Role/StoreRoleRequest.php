<?php

namespace App\Http\Requests\V1\Role;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => 'required|string|min:3|max:50|unique:mst_roles,name',
            'privileges' => 'nullable|array',
            'privileges.*' => 'integer|exists:lov_privileges,id',
            'is_editable' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'name.required' => 'Role name is required.',
            'name.min' => 'Role name must be at least 3 characters.',
            'name.max' => 'Role name may not be greater than 50 characters.',
            'name.unique' => 'Role name already exists.',
            'privileges.array' => 'Privileges must be an array.',
            'privileges.*.integer' => 'Each privilege must be a valid integer.',
            'privileges.*.exists' => 'One or more selected privileges do not exist.',
            'is_editable.boolean' => 'Is editable must be true or false.',
            'is_active.boolean' => 'Is active must be true or false.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'name' => 'role name',
            'privileges' => 'privileges',
            'is_editable' => 'is editable',
            'is_active' => 'is active',
        ];
    }
}
