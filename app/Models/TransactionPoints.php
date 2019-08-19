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
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'used_at',
        'refunded_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
        'used_at' => 'datetime:Y-m-d H:i:s',
        'refunded_at' => 'datetime:Y-m-d H:i:s',
    ];

    /**
     * Get the Transaction that owns the TransactionPoints.
     */
    public function transaction()
    {
        return $this->belongsTo('App\Models\Transaction');
    }

    /**
     * Get the VoucherInstancePoints for the TransactionPoints.
     */
    public function voucherInstancePoints(){
        return $this->hasMany('App\Models\VoucherInstancePoints');
    }

    public function getAvailablePointsAttribute(){
        return $this->attributes['original'] - $this->attributes['redeemed'];
    }

    public function getIsValidAttribute(){
        if (!$status = $this->status) { throw new Exception("cannot determine transaction points status"); }
        else { return $status==config('constants.status_codes.valid_status'); }
    }

    public function getIsPendingAttribute(){
        if (!$status = $this->status) { throw new Exception("cannot determine transaction points status"); }
        else { return $status==config('constants.status_codes.pending_status'); }
    }

    public function getIsExpiredAttribute(){
        if (!$status = $this->status) { throw new Exception("cannot determine transaction points status"); }
        else { return $status==config('constants.status_codes.expired_status'); }
    }

    public function getStatusAttribute(){

        $status = config('constants.status_codes.status_error');

        if ($this->is_used) { $status = config('constants.status_codes.used_status'); }
        else if ($this->refunded) { $status = config('constants.status_codes.refunded_status'); }
        else {

            $pendingDuration = $this->getSetting(config('constants.settings.pending_duration'))->value;
            $validDuration = $this->getSetting(config('constants.settings.valid_duration'))->value;
            if ($pendingDuration && $validDuration) {

                $pendingDurationEndDate = $this->created_at->addDays($pendingDuration);
                $validDurationEndDate = $pendingDurationEndDate->copy()->addDays($validDuration);

                if ($this->isPending($pendingDurationEndDate)) { $status = config('constants.status_codes.pending_status'); }
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
        $pendingDuration = $this->getSetting(config('constants.settings.pending_duration'))->value;
        return $this->created_at->addDays($pendingDuration)->toDateTimeString();

    }

    public function getValidEndDateAttribute(){
        $pendingDuration = $this->getSetting(config('constants.settings.pending_duration'))->value;
        $validDuration = $this->getSetting(config('constants.settings.valid_duration'))->value;
        $pendingDurationEndDate = $this->created_at->addDays($pendingDuration);
        return $pendingDurationEndDate->addDays($validDuration)->toDateTimeString();
    }

}
