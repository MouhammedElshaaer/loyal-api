<?php

namespace App\Http\Requests;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\App;

use App\Http\Requests\Traits\UsesCustomErrorMessage;

use App\Models\Configuration;

class FetchSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        foreach($this->data['settings'] as $config=>$value){
            //We use raw() here as we want a case sensitive comparison
            if(!Configuration::where(\DB::raw("BINARY `category`"), $config)->first()){return false;}
        }
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
        $locale = $this->headers->get('locale');
        App::setLocale($locale);
        return  __('validation.custom');
    }

    public function message()
    {
        return __('messages.validation_error');
    }
}
