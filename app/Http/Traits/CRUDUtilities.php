<?php

namespace App\Http\Traits;

use Illuminate\Http\Exceptions\HttpResponseException;

trait CRUDUtilities
{

    public function createUpdateDataRow($dataTypePath, $attributes){

        $dataRow = null;
        if(!key_exists('id', $attributes) || !$attributes['id']) { $dataRow = ($dataTypePath)::create($attributes); }
        else {
            $dataRowId = $attributes['id'];
            unset($attributes['id']);
            $dataRow = ($dataTypePath)::find($dataRowId)->update($attributes);
        }
        
        return $dataRow;
    }

    public function getDataRow($dataTypePath, $key, $value){

        return ($dataTypePath)::where($key, $value)->first();
    }

    public function getDataRows($dataTypePath, $key, $value){

        return ($dataTypePath)::where($key, $value)->get();
    }
}