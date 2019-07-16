<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoucherInstancePoints extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'voucher_instance_id',
        'transaction_points_id',
        'amount'
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
    protected $appends = [];

    /**
     * Get the TransactionPoints that owns the TransactionPointsVoucherInstance.
     */
    public function transactionPoints()
    {
        return $this->belongsTo('App\Models\TransactionPoints');
    }

    /**
     * Get the VoucherInstance that owns the TransactionPointsVoucherInstance.
     */
    public function voucherInstance()
    {
        return $this->belongsTo('App\Models\VoucherInstance');
    }
}
