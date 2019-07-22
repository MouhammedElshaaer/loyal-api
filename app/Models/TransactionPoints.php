<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Http\Traits\SettingUtilities;
use App\Http\Traits\StatusUtilities;

use Exception;

class TransactionPoints extends Model
{
    use SettingUtilities, StatusUtilities;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'transaction_id',
        'original',
        'redeemed',
        'used_at',
        'refunded_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'available_points',
        'pending_end_date',
        'valid_end_date',
        'status',
        'is_used',
        'is_refunded',
        'is_valid',
        'is_pending',
        'is_expired'
    ];

    /**
     * Get the VoucherInstancePoints for the TransactionPoints.
     */
    public function voucherInstancePoints(){
        return $this->hasMany('App\Models\VoucherInstancePoints');
    }

    public function getAvailablePointsAttribute(){
        return (int) $this->original - (int) $this->redeemed;
    }

    public function getIsValidAttribute(){
        if (!$status = $this->status) { throw new Exception("cannot determine transaction points status"); }
        else { return $status==__('constants.valid_status'); }
    }

    public function getIsPendingAttribute(){
        if (!$status = $this->status) { throw new Exception("cannot determine transaction points status"); }
        else { return $status==__('constants.pending_status'); }
    }

    public function getIsExpiredAttribute(){
        if (!$status = $this->status) { throw new Exception("cannot determine transaction points status"); }
        else { return $status==__('constants.expired_status'); }
    }

    public function getStatusAttribute(){

        $status = __('constants.status_error');

        if ($this->used) { $status = __('constants.used_status'); }
        else if ($this->refunded) { $status = __('constants.refunded_status'); }
        else {

            $pendingDuration = $this->getSetting(__('constants.pending_duration'))->value;
            $validDuration = $this->getSetting(__('constants.valid_duration'))->value;
            if ($pendingDuration && $validDuration) {

                $pendingDurationEndDate = $this->created_at->addDays($pendingDuration);
                $validDurationEndDate = $pendingDurationEndDate->copy()->addDays($validDuration);

                if ($this->isPending($pendingDurationEndDate)) { $status = __('constants.pending_status'); }
                else { $status = $this->getValidityStatus($this->isValid($validDurationEndDate)); }
            }
        }
        return $status;
    }

    public function getIsUsedAttribute(){
        return $this->used_at? true: false;
    }

    public function getIsRefundedAttribute(){
        return $this->refunded_at? true: false;
    }

    public function getPendingEndDateAttribute(){
        $pendingDuration = $this->getSetting(__('constants.pending_duration'))->value;
        return $this->created_at->addDays($pendingDuration)->toDateTimeString();
         
    }

    public function getValidEndDateAttribute(){
        $pendingDuration = $this->getSetting(__('constants.pending_duration'))->value;
        $validDuration = $this->getSetting(__('constants.valid_duration'))->value;
        $pendingDurationEndDate = $this->created_at->addDays($pendingDuration);
        return $pendingDurationEndDate->addDays($validDuration)->toDateTimeString();    
    }

}
