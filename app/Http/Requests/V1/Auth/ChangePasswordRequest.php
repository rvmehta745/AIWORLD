<?php

namespace App\Http\Requests\V1\Auth;

use App\Library\General;
use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
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
            'current_password'      => 'required',
            'password'              => 'required|min:' . General::$passwordMin,
            //'password_confirmation' => 'required|min:' . General::$passwordMin,
        ];
    }

    public function messages()
    {
        return [
            'current_password.required'      => __('validation.required.text', ['attribute' => __('labels.current_password')]),
            'password.required'              => __('validation.required.text', ['attribute' => __('labels.password')]),
           // 'password.confirmed'             => __('validation.confirmed', ['attribute' => __('labels.password'), 'otherAttribute' => __('labels.confirm_password')]),
            'password.min'                   => __('validation.min.string', ['attribute' => __('labels.password')]),
            //'password_confirmation.required' => __('validation.required.text', ['attribute' => __('labels.confirm_password')]),
            //'password_confirmation.min'      => __('validation.min.string', ['attribute' => __('labels.confirm_password')]),
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new \Illuminate\Validation\ValidationException($validator, General::setResponse("VALIDATION_ERROR", $validator->errors()));
    }
}
