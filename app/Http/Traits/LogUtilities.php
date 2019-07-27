<?php

namespace App\Http\Traits;

use Illuminate\Http\Exceptions\HttpResponseException;

use App\Models\Scope;
use App\Models\Action;

trait LogUtilities
{
    public function initLogAttributes($userId, $dataRowId, $dataType, $scopeName, $actionType){

        return [
            'user_id' => $userId,
            'data_row_id' => $dataRowId,
            'data_type' => $dataType,
            'scope_id' => $this->getScope(config('constants.scopes.'.$scopeName))->id,
            'action_id' => $this->getAction($actionType)->id
        ];
    }

    public function updateLogAttributes($actionLogAttributes, $attributes){

        foreach ($attributes as $key=>$value) { $actionLogAttributes[$key] = $value; }
        return $actionLogAttributes;
    }

    public function getScope($scopeName){
        return $this->getDataRowByKey(Scope::class, 'name', $scopeName);
    }

    public function getAction($actionType){
        return $this->getDataRowByKey(Action::class, 'type', $actionType);
    }

    public function resolveActionFromStatus($actionScope, $status){

        $actionType = $actionScope.'_'.$status;
        return config('constants.actions.'.$actionType);

    }
}