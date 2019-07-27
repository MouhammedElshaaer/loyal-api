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

    public function getDataRowByPrimaryKey($dataTypePath, $PrimaryKey){

        return ($dataTypePath)::find($PrimaryKey);
    }

    public function getDataRowByKey($dataTypePath, $key, $value){

        return ($dataTypePath)::where($key, $value)->first();
    }

    public function getDataRows($dataTypePath, $key, $value){

        return ($dataTypePath)::where($key, $value)->get();
    }

    public function getAllDataRows($dataTypePath){

        return ($dataTypePath)::all();
    }

    public function deleteDataRow($dataTypePath, $dataTypeId){

        $dataType = ($dataTypePath)::find($dataTypeId);
        if (!$dataType){return false;}
        $dataType->delete();
        return true;
    }
}