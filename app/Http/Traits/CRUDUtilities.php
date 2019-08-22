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
            ($dataTypePath)::find($dataRowId)->update($attributes);
            $dataRow = ($dataTypePath)::find($dataRowId);
        }

        return $dataRow;
    }

    public function getDataRowByPrimaryKey($dataTypePath, $PrimaryKey){

        return ($dataTypePath)::find($PrimaryKey);
    }

    public function getDataRow($dataTypePath, $key, $value){

        return ($dataTypePath)::where($key, $value)->first();
    }

    public function getDataRows($dataTypePath, $key, $value){

        return ($dataTypePath)::where($key, $value)->get();
    }

    public function getAllDataRows($dataTypePath){

        return ($dataTypePath)::all();
    }

    public function deleteDataRowByPrimaryKey($dataTypePath, $dataRowId){

        $dataType = ($dataTypePath)::find($dataRowId);
        if (!$dataType){return false;}
        $dataType->delete();
        return true;
    }

    public function deleteDataRow($dataTypePath, $key, $value){

        $dataType = ($dataTypePath)::where($key, $value)->first();
        if (!$dataType){return false;}
        $dataType->delete();
        return true;
    }
}
