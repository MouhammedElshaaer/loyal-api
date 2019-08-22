<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Http\Traits\SettingUtilities;
use App\Http\Traits\StatusUtilities;

use App\User;
use App\Models\Voucher;

class VoucherInstance extends Model
{
    use SettingUtilities, StatusUtilities;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'voucher_id',
        'transaction_id',
        'qr_code',
        'used_at'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'deactivated',
        'created_at',
        'updated_at'
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'valid_end_date',
        'status',
        'is_used',
        'is_valid',
        'is_expired',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'used_at'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime:l jS \\of F Y h:i:s A',
        'updated_at' => 'datetime:l jS \\of F Y h:i:s A',
        'used_at' => 'datetime:l jS \\of F Y h:i:s A'
    ];

    public static function create(array $attributes = []){
        $model = static::query()->create($attributes);

        $voucher = Voucher::find($attributes['voucher_id']);
        $user = User::find($attributes['user_id']);

        $voucherPoints = $voucher->points;
        foreach ($user->transactions as $transaction) {

            $transactionPoints = $transaction->transactionPoints;
            if ($voucherPoints > 0 && $transactionPoints->is_valid) {

                $availablePoints = $transactionPoints->available_points;
                $neededPoints = null;

                if ($voucherPoints<$availablePoints) { $neededPoints = $voucherPoints; }
                else {
                    $neededPoints = $availablePoints;
                    $transactionPoints->used_at = \Carbon\Carbon::now();
                }

                if ($neededPoints) {
                    $voucherPoints -= $neededPoints;
                    $transactionPoints->redeemed += $neededPoints;
                    $transactionPoints->save();
                    $viPointsAttributes = [
                        'voucher_instance_id' => $model->id,
                        'transaction_points_id' => $transactionPoints->id,
                        'amount' => $neededPoints,
                    ];
                    $voucherInstancePoints = VoucherInstancePoints::create($viPointsAttributes);
                } else { throw new Exception('Failed create voucherInstancePoints'); }
            }

        }
        return $model;
    }

    /**
     * Get the VoucherInstancePoints for the TransactionPoints.
     */
    public function voucherInstancePoints(){
        return $this->hasMany('App\Models\VoucherInstancePoints');
    }

    public function getIsValidAttribute(){
        if (!$status = $this->status) { throw new Exception("cannot determine voucher instance status"); }
        else { return $status==config('constants.status_codes.valid_status'); }
    }

    public function getIsExpiredAttribute(){
        if (!$status = $this->status) { throw new Exception("cannot determine transaction points status"); }
        else { return $status==config('constants.status_codes.expired_status'); }
    }

    /**
     * Get the voucher instance status code.
     *
     * @return int
     */
    public function getStatusAttribute(){

        $status = config('constants.status_codes.status_error');

        if ($this->is_used) { $status = config('constants.status_codes.used_status'); }
        else {
            $validDuration = $this->getSetting(config('constants.settings.valid_duration'))->value;
            if ($validDuration) {
                $validDurationEndDate = $this->created_at->addDays($validDuration);
                $status = $this->getValidityStatus($this->isValid($validDurationEndDate));
            }
        }
        return $status;
    }

    public function getIsUsedAttribute(){
        return $this->used_at? true: false;
    }

    public function getValidEndDateAttribute(){
        $validDuration = $this->getSetting(config('constants.settings.valid_duration'))->value;
        return $this->created_at->addDays($validDuration)->toDateTimeString();

    }

    /**
     * Get the Voucher that owns the VoucherInstance.
     */
    public function voucher()
    {
        return $this->belongsTo('App\Models\Voucher');
    }

    /**
     * Get the User that owns the VoucherInstance.
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    /**
     * Get the Transaction that owns the used VoucherInstance.
     */
    public function transaction()
    {
        return $this->belongsTo('App\Models\Transaction');
    }
}
