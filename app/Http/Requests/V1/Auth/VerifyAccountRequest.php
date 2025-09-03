<?php

namespace App\Http\Requests\V1\Auth;

use App\Library\General;
use Illuminate\Foundation\Http\FormRequest;

class VerifyAccountRequest extends FormRequest
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
            'token' => "required",
        ];
    }
    
    // protected function prepareForValidation()
    // {
    //     $this->merge([
    //         'token' => $this->route('token')
    //     ]);
    // }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'token.required' => __('validation.required.text', ['attribute' => __('labels.token')]),
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new \Illuminate\Validation\ValidationException($validator, General::setResponse("VALIDATION_ERROR", $validator->errors()));
    }
}
