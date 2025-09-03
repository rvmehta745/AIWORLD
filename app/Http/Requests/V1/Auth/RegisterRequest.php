<?php

namespace App\Http\Requests\V1\Auth;

use App\Library\General;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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

    public function rules()
    {
        return [
            'first_name' => 'required|string|min:2|max:50',
            'last_name' => 'required|string|min:2|max:50',
            'email' => 'required|string|email|max:100|unique:mst_users,email,NULL,id,deleted_at,NULL',
            'password' => 'required|string|min:8',
            'phone_number' => 'nullable|string|max:20',
            'country_code' => 'nullable|string|max:10',
            'role' => 'required|in:Admin,Disposition Manager,Buyer|not_in:Admin'
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
            'first_name.required' => 'The first name is required',
            'first_name.min' => 'The first name must be at least 2 characters',
            'first_name.max' => 'The first name cannot exceed 50 characters',
            'last_name.required' => 'The last name is required',
            'last_name.min' => 'The last name must be at least 2 characters',
            'last_name.max' => 'The last name cannot exceed 50 characters',
            'email.required' => 'The email address is required',
            'email.email' => 'Please provide a valid email address',
            'email.unique' => 'This email address is already registered',
            'password.required' => 'The password is required',
            'password.min' => 'The password must be at least 8 characters',
            'phone_number.max' => 'The phone number cannot exceed 20 characters',
            'country_code.max' => 'The country code cannot exceed 10 characters',
            'role.required' => 'The role is required',
            'role.not_in' => 'Admin can not be registered',
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new \Illuminate\Validation\ValidationException($validator, General::setResponse("VALIDATION_ERROR", $validator->errors()));
    }
}
