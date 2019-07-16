<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'transaction_points_id',
        'invoice_number',
        'invoice_value'
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
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    /**
     * Get the TransactionPoints record associated with the Transaction.
     */
    public function transactionPoints()
    {
        return $this->hasOne('App\Models\TransactionPoints');
    }
}
