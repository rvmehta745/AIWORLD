<?php

namespace App\Http\Requests\V1\Auth;

use App\Library\General;
use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
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
       
        $otp = $this->input('otp');
        
        $this->merge([
            'otp' => $otp,
        ]);

        $decodedEmail = base64_decode($this->input('email'));

        $this->merge([
            'email' => $decodedEmail,
        ]);
        
        
        return [
            'email' => "required|exists:mst_users,email",
            'otp' => "required|exists:password_reset_tokens,token",
        ];
    }

    public function messages()
    {
        return [
            'otp.required' => __('validation.required.text', ['attribute' => 'OTP']),
            'otp.exists'   => __('validation.invalid_otp'),
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new \Illuminate\Validation\ValidationException($validator, General::setResponse("VALIDATION_ERROR", $validator->errors()));
    }
}