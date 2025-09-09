<?php

namespace App\Http\Requests\V1\User;

use Illuminate\Foundation\Http\FormRequest;

class StoreDispositionManagerRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:mst_users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|string|in:Super Admin,Admin,Users',
            'phone_number' => 'nullable|string|max:20',
            'country_code' => 'nullable|string|max:5',
        ];
    }
}