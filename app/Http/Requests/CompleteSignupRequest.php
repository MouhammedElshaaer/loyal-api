<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\App;

use App\Http\Requests\Traits\UsesCustomErrorMessage;

class CompleteSignupRequest extends FormRequest
{
    use UsesCustomErrorMessage;

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
     * @return array
     */
    public function rules()
    {
        return [
            'provider_name' => 'required|string',
            'password' => 'required|min:8',
            'country_code' => 'required',
            'phone' => 'required|unique:users|numeric|digits_between:8,14'
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        $locale = $this->headers->get('locale');
        App::setLocale($locale);
        return  __('validation.custom');
    }
}
