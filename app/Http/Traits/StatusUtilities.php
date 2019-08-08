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
        return $isValid? config('constants.status_codes.valid_status'): config('constants.status_codes.expired_status');
    }

    public function resolveStatusFromStatusCode($statusCode){
        return config('constants.status.'.$statusCode);
    }
    public function getPendingVersion($transactionPoints){

        $transactionPointsCopy = $transactionPoints->toArray();

        $transactionPointsCopy['is_pending'] = true;
        $transactionPointsCopy['is_used'] = false;
        $transactionPointsCopy['is_refunded'] = false;
        $transactionPointsCopy['is_valid'] = false;
        $transactionPointsCopy['is_expired'] = false;

        $statusCode = config('constants.status_codes.pending_status');

        $transactionPointsCopy['status'] = config('constants.status.'.$statusCode);

        return $transactionPointsCopy;
    }

    public function getUsedVersion($transactionPoints){

        $transactionPointsCopy = $transactionPoints->toArray();

        $transactionPointsCopy['is_pending'] = false;
        $transactionPointsCopy['is_used'] = true;
        $transactionPointsCopy['is_refunded'] = false;
        $transactionPointsCopy['is_valid'] = false;
        $transactionPointsCopy['is_expired'] = false;

        $statusCode = config('constants.status_codes.used_status');

        $transactionPointsCopy['status'] = config('constants.status.'.$statusCode);

        return $transactionPointsCopy;
    }

    public function getRefundedVersion($transactionPoints){

        $transactionPointsCopy = $transactionPoints->toArray();

        $transactionPointsCopy['is_pending'] = false;
        $transactionPointsCopy['is_used'] = false;
        $transactionPointsCopy['is_refunded'] = true;
        $transactionPointsCopy['is_valid'] = false;
        $transactionPointsCopy['is_expired'] = false;

        $statusCode = config('constants.status_codes.refunded_status');

        $transactionPointsCopy['status'] = config('constants.status.'.$statusCode);

        return $transactionPointsCopy;
    }

    public function getValidVersion($transactionPoints){

        $transactionPointsCopy = $transactionPoints->toArray();

        $transactionPointsCopy['is_pending'] = false;
        $transactionPointsCopy['is_used'] = false;
        $transactionPointsCopy['is_refunded'] = false;
        $transactionPointsCopy['is_valid'] = true;
        $transactionPointsCopy['is_expired'] = false;

        $statusCode = config('constants.status_codes.valid_status');

        $transactionPointsCopy['status'] = config('constants.status.'.$statusCode);

        return $transactionPointsCopy;
    }

    public function getExpiredVersion($transactionPoints){

        $transactionPointsCopy = $transactionPoints->toArray();

        $transactionPointsCopy['is_pending'] = false;
        $transactionPointsCopy['is_used'] = false;
        $transactionPointsCopy['is_refunded'] = false;
        $transactionPointsCopy['is_valid'] = false;
        $transactionPointsCopy['is_expired'] = true;

        $statusCode = config('constants.status_codes.expired_status');

        $transactionPointsCopy['status'] = config('constants.status.'.$statusCode);

        return $transactionPointsCopy;
    }
}
