<?php

namespace App\Http\Traits;

use Illuminate\Http\Exceptions\HttpResponseException;

use Carbon\Carbon;

trait StatusUtilities
{
    public function isPending($pendingDurationEndDate){
        return $pendingDurationEndDate->diffInHours(Carbon::now(), false) < 0;
    }

    public function isValid($validDurationEndDate){
        return $validDurationEndDate->diffInHours(Carbon::now(), false) < 0;
    }

    public function getValidityStatus($isValid){
        return $isValid? config('constants.status.valid_status'): config('constants.status.expired_status');
    }
}