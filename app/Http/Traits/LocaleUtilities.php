<?php

namespace App\Http\Traits;

use Illuminate\Http\Exceptions\HttpResponseException;

use Exception;

use App\Models\Translation;

trait LocaleUtilities
{
    public function locale($locale){
        return count($this->locales)>0 ? $this->locales->where('locale', $locale) :null;
    }

    public function value($locale, $field){
        if($locale = $this->locale($locale)){
            if($field = $locale->where('data_field', $field)->first()){return $field->value;}
        }
        return null;
    }

    public function storeLocales($locales, $data_row_id, $data_type_path){

        try{
            foreach($locales as $dataRow){

                $locale = $dataRow['locale'];
                unset($dataRow['locale']);

                foreach($dataRow as $field=>$value){
                    $this->createLocaleInstance($data_row_id, $data_type_path, $locale, $field, $value);
                }
            }
        }catch(Exception $e){return false;}
        return true;
    }

    public function updateLocales($locales, $data_row_id, $data_type_path){

        try{
            $dataTypeRow = ($data_type_path)::find($data_row_id);
            $query = $dataTypeRow->locales();
            foreach($locales as $updatedDataRow){

                $locale = $updatedDataRow['locale'];
                unset($updatedDataRow['locale']);

                $queryCopy = clone $query;
                $localeFilteredQuery = $queryCopy->where('locale', $locale);

                foreach($updatedDataRow as $field=>$value){
                    $localeFilteredQueryCopy = clone $localeFilteredQuery;
                    $dataRow = $localeFilteredQueryCopy->where('data_field', $field)->first();
                    if(!$dataRow){$this->createLocaleInstance($data_row_id, $data_type_path, $locale, $field, $value);}
                    else{
                        $dataRow->value = $value;
                        $dataRow->save();
                    }
                }
            }
        }catch(Exception $e){return false;}
        return true;
    }

    public function deleteLocales($data_row_id, $data_type_path){
        try{($data_type_path)::find($data_row_id)->locales()->delete();}
        catch(Exception $e){return false;}
        return true;
    }

    public function createLocaleInstance($data_row_id, $data_type_path, $locale, $field, $value){
        try{
            $newDataRow = new Translation;
            $newDataRow->data_row_id = $data_row_id;
            $newDataRow->data_type = $data_type_path;
            $newDataRow->data_field = $field;
            $newDataRow->value = $value;
            $newDataRow->locale = $locale;
            $newDataRow->save();
        }
        catch(Exception $e){return false;}
        return true;
    }
}