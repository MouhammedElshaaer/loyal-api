<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\App;

use App\Http\Requests\Traits\UsesCustomErrorMessage;

class CreateUserRequest extends FormRequest
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
            'name' => 'required',
            'email' => 'email|required|unique:users',
            'password' => 'required|min:8',
            'phone' => 'required|unique:users',
            'country_code' => 'required',
            'image' => 'required',
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

    public function message()
    {
        return __('messeges.validation_error');
    }

}
