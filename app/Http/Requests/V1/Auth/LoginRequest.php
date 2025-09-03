<?php

namespace App\Http\Requests\V1\Auth;

use App\Library\General;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
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
            'email' => "required",
            'password' => "required|min:" . General::$passwordMin . "|max:" . General::$passwordMax,
        ];
    }

    public function messages()
    {
        return [
            'email.required' => __('validation.required.text', ['attribute' => __('labels.email')]),
            'password.required' => __('validation.required.text', ['attribute' => __('labels.password')]),
            'password.min'      => __('validation.min.string', ['attribute' => __('labels.password')]),
            'password.max'      => __('validation.max.string', ['attribute' => __('labels.password')]),
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new \Illuminate\Validation\ValidationException($validator, General::setResponse("VALIDATION_ERROR", $validator->errors()));
    }
}
