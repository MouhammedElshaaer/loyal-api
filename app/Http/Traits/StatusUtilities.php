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

        $invoice_number = $transactionPoints->transaction->invoice_number;
        $transactionPointsCopy = $transactionPoints->toArray();

        $transactionPointsCopy['redeemed'] = 0;
        $transactionPointsCopy['available_points'] = $transactionPointsCopy['original'];
        $transactionPointsCopy['invoice_number'] = $invoice_number;

        $transactionPointsCopy['is_pending'] = true;
        $transactionPointsCopy['is_used'] = false;
        $transactionPointsCopy['is_refunded'] = false;
        $transactionPointsCopy['is_valid'] = false;
        $transactionPointsCopy['is_expired'] = false;

        $statusCode = config('constants.status_codes.pending_status');

        $transactionPointsCopy['status'] = config('constants.status.'.$statusCode);

        unset($transactionPointsCopy['voucher_instance_points']);
        unset($transactionPointsCopy['transaction']);
        return $transactionPointsCopy;
    }

    public function getUsedVersions($transactionPoints){

        $usedVersions = [];
        $available_points = $transactionPoints->original;
        foreach($transactionPoints->voucherInstancePoints as $voucherInstancePoint){

            $invoice_number = $transactionPoints->transaction->invoice_number;
            $transactionPointsCopy = $transactionPoints->toArray();

            $amount = $voucherInstancePoint->amount;
            $available_points -= $amount;
            $transactionPointsCopy['redeemed'] = $amount;
            $transactionPointsCopy['available_points'] = $available_points;
            $transactionPointsCopy['invoice_number'] = $invoice_number;

            $transactionPointsCopy['is_pending'] = false;
            $transactionPointsCopy['is_used'] = true;
            $transactionPointsCopy['is_refunded'] = false;
            $transactionPointsCopy['is_valid'] = false;
            $transactionPointsCopy['is_expired'] = false;

            $transactionPointsCopy['used_at'] = $voucherInstancePoint->created_at->toDateTimeString();

            $statusCode = config('constants.status_codes.used_status');
            $transactionPointsCopy['status'] = config('constants.status.'.$statusCode);

            unset($transactionPointsCopy['voucher_instance_points']);
            unset($transactionPointsCopy['transaction']);
            $usedVersions[] = $transactionPointsCopy;
        }
        return array_reverse($usedVersions);
    }

    public function getRefundedVersion($transactionPoints){

        $invoice_number = $transactionPoints->transaction->invoice_number;
        $transactionPointsCopy = $transactionPoints->toArray();

        $transactionPointsCopy['invoice_number'] = $invoice_number;

        $transactionPointsCopy['is_pending'] = false;
        $transactionPointsCopy['is_used'] = false;
        $transactionPointsCopy['is_refunded'] = true;
        $transactionPointsCopy['is_valid'] = false;
        $transactionPointsCopy['is_expired'] = false;

        $statusCode = config('constants.status_codes.refunded_status');

        $transactionPointsCopy['status'] = config('constants.status.'.$statusCode);

        unset($transactionPointsCopy['voucher_instance_points']);
        unset($transactionPointsCopy['transaction']);
        return $transactionPointsCopy;
    }

    public function getValidVersion($transactionPoints){

        $invoice_number = $transactionPoints->transaction->invoice_number;
        $transactionPointsCopy = $transactionPoints->toArray();

        $transactionPointsCopy['redeemed'] = 0;
        $transactionPointsCopy['available_points'] = $transactionPointsCopy['original'];
        $transactionPointsCopy['invoice_number'] = $invoice_number;

        $transactionPointsCopy['is_pending'] = false;
        $transactionPointsCopy['is_used'] = false;
        $transactionPointsCopy['is_refunded'] = false;
        $transactionPointsCopy['is_valid'] = true;
        $transactionPointsCopy['is_expired'] = false;

        $statusCode = config('constants.status_codes.valid_status');

        $transactionPointsCopy['status'] = config('constants.status.'.$statusCode);

        unset($transactionPointsCopy['voucher_instance_points']);
        unset($transactionPointsCopy['transaction']);
        return $transactionPointsCopy;
    }

    public function getExpiredVersion($transactionPoints){

        $invoice_number = $transactionPoints->transaction->invoice_number;
        $transactionPointsCopy = $transactionPoints->toArray();

        $transactionPointsCopy['invoice_number'] = $invoice_number;

        $transactionPointsCopy['is_pending'] = false;
        $transactionPointsCopy['is_used'] = false;
        $transactionPointsCopy['is_refunded'] = false;
        $transactionPointsCopy['is_valid'] = false;
        $transactionPointsCopy['is_expired'] = true;

        $statusCode = config('constants.status_codes.expired_status');

        $transactionPointsCopy['status'] = config('constants.status.'.$statusCode);

        unset($transactionPointsCopy['voucher_instance_points']);
        unset($transactionPointsCopy['transaction']);
        return $transactionPointsCopy;
    }
}
