<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionPoints extends Model
{
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
    protected $appends = [];

    /**
     * Get the VoucherInstancePoints for the TransactionPoints.
     */
    public function voucherInstancePoints()
    {
        return $this->hasMany('App\Models\VoucherInstancePoints');
    }
}
