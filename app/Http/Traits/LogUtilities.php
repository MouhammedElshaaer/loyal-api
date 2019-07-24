<?php

namespace App\Http\Traits;

use Illuminate\Http\Exceptions\HttpResponseException;

use App\Models\Scope;
use App\Models\Action;

trait LogUtilities
{
    public function initLogAttributes($userId, $dataRowId, $dataType, $scope, $action){

        return [
            'user_id' => $userId,
            'data_row_id' => $dataRowId,
            'data_type' => $dataType,
            'scope_id' => $this->getDataRowByKey(Scope::class, 'name', config('constants.scopes.'.$scope))->id,
            'action_id' => $this->getDataRowByKey(Action::class, 'type', config('constants.actions.'.$action))->id
        ];
    }

    public function updateLogAttributes($actionLogAttributes, $attributes){

        foreach ($attributes as $key=>$value){
            $actionLogAttributes[$key] = $value;
        }
        return $actionLogAttributes;
    }
}