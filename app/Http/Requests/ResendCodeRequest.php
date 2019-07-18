<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\App;

use App\Http\Requests\Traits\UsesCustomErrorMessage;

class ResendCodeRequest extends FormRequest
{
    use UsesCustomErrorMessage;
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $locale = $this->headers->get('locale');
        App::setLocale($locale);
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
            'phone' => 'required|numeric|digits_between:8,14',
            'country_code' => 'required',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return  __('validation.custom');
    }

    public function message()
    {
        return __('messeges.validation_error');
    }
}
