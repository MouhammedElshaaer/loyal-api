<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Carbon\Carbon;

class VoucherInstance extends Model
{

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'user_id',
        'voucher_id',
        'qr_code',
        'used_at',
        'transaction_id',
        'deactivated',
        'created_at',
        'updated_at'
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['status'];

    /**
     * Get the voucher instance status.
     *
     * @return bool
     */
    public function getStatus()
    {
        /**
         * Here we should get the expiration_duration from configs table
         */
        $expiration_duration = 14;

        $current_date = $this->attributes['created_at']->addDays($expiration_duration);
        $diffInDays = $current_date->diffInDays(Carbon::now());

        return $diffInDays > 0? "valid": "expired";
    }

    /**
     * Get the VoucherInstancePoints for the TransactionPoints.
     */
    public function voucherInstancePoints()
    {
        return $this->hasMany('App\Models\VoucherInstancePoints');
    }
}
