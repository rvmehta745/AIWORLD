<?php

namespace App\Http\Requests\V1\Auth;

use App\Library\General;
use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordOtpRequest extends FormRequest
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
            'email' => "required|email|exists:mst_users,email",
        ];
    }

    public function messages()
    {
        return [
            'email.required' => __('validation.required.text', ['attribute' => __('labels.email')]),
            'email.email'    => __('validation.email', ['attribute' => __('labels.email')]),
            'email.exists'   => __('messages.email_exist', ['attribute' => __('labels.email')])
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new \Illuminate\Validation\ValidationException($validator, General::setResponse("VALIDATION_ERROR", $validator->errors()));
    }
}
