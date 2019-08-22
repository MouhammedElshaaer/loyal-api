<?php

namespace App\Http\Requests;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\App;

use App\Http\Requests\Traits\UsesCustomErrorMessage;

use App\Models\Configuration;

class FetchSettingsRequest extends FormRequest
{
    use UsesCustomErrorMessage;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        foreach($this->settings as $config=>$value){
            //We use raw() here as we want a case sensitive comparison
            if(!Configuration::where(\DB::raw("BINARY `category`"), config('constants.settings.'.$config))->first()){return false;}
        }
        App::setLocale($this->headers->get('locale'));
        return true;
    }

    protected function failedAuthorization()
    {
        throw new AuthorizationException();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [

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
}
