<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Passport\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

use App\Models\LinkedSocialAccount;

use Carbon\Carbon;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'phone', 'country_code', 'image'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'verified',
        'total_points',
        'total_expire',
        'latest_expire',
        'otp',
        'deactivated',
        'user_verify_otp',
        'mobile_verify_otp',
        'created_at',
        'updated_at'
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'total_points',
        'total_expire',
        'latest_expire'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function linkedSocialAccounts()
    {
        return $this->hasMany(LinkedSocialAccount::class);
    }

    /**
     * Get the voucher instance status.
     *
     * @return bool
     */
    public function getTotalPointsAttribute()
    {
        /**
         * Here we should calculate all user valid points from transactions table
         */

        return 1300;
    }
    /**
     * Get the voucher instance status.
     *
     * @return bool
     */
    public function getTotalExpireAttribute()
    {
        /**
         * Here we should calculate all user valid points from transactions table
         */

        return 500;
    }
    /**
     * Get the voucher instance status.
     *
     * @return bool
     */
    public function getLatestExpireAttribute()
    {
        /**
         * Here we should calculate all user valid points from transactions table
         */

        return Carbon::now()->toFormattedDateString();
    }

    /**
     * Get the Transactions for the User.
     */
    public function transactions()
    {
        return $this->hasMany('App\Models\Transaction');
    }

    /**
     * Get the VoucherInstances for the User.
     */
    public function voucherInstances()
    {
        return $this->hasMany('App\Models\VoucherInstance');
    }
}
