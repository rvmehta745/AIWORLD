<?php

namespace App\Http\Requests\V1\Auth;

use App\Library\General;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
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
        $id = \Auth::id(); // get logged-in user id

        return [
            'first_name' => "nullable|min:" . General::$firstnameMin . "|max:" . General::$firstnameMax,
            'last_name'  => "nullable|min:" . General::$lastnameMin . "|max:" . General::$lastnameMax,
            'email'      => "nullable|email|unique:mst_users,email,$id,id,deleted_at,NULL|min:" . General::$emailMin . "|max:" . General::$emailMax,
            'phone_number'  => "nullable|min:" . General::$mobMin . "|max:" . General::$mobMax,
            'country_code'  => "nullable|string|max:20",
            'address'    => "nullable|string|max:255",
            'photo'      => "nullable|image|mimes:" . General::$imageMimes . "|max:" . General::$imageSize,
        ];
    }


    public function messages()
    {
        return [
            'first_name.min'      => __('validation.min.string', ['attribute' => __('labels.first_name')]),
            'first_name.max'      => __('validation.max.string', ['attribute' => __('labels.first_name')]),
            'last_name.min'       => __('validation.min.string', ['attribute' => __('labels.last_name')]),
            'last_name.max'       => __('validation.max.string', ['attribute' => __('labels.last_name')]),
            'email.email'         => __('validation.email', ['attribute' => __('labels.email')]),
            'email.unique'        => __('validation.unique', ['attribute' => __('labels.email')]),
            'email.min'           => __('validation.min.string', ['attribute' => __('labels.email')]),
            'email.max'           => __('validation.max.string', ['attribute' => __('labels.email')]),
            'phone_number.min'       => __('validation.min.string', ['attribute' => __('labels.phone_number')]),
            'phone_number.max'       => __('validation.max.string', ['attribute' => __('labels.phone_number')]),
            'country_code.max'       => __('validation.max.string', ['attribute' => __('labels.country_code')]),

            'address.max'         => __('validation.max.string', ['attribute' => __('labels.address')]),
            'photo.image'         => __('validation.image', ['attribute' => __('labels.photo')]),
            'photo.mimes'         => __('validation.mimes', ['attribute' => __('labels.photo'), 'values' => General::$imageMimes]),
            'photo.max'           => __('validation.max.file', ['attribute' => __('labels.photo'), 'max' => General::$imageSize]),
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new \Illuminate\Validation\ValidationException($validator, General::setResponse("VALIDATION_ERROR", $validator->errors()));
    }
}
