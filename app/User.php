<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Passport\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

use App\Http\Traits\CodeGenerationUtilities;

use App\Models\LinkedSocialAccount;

use Carbon\Carbon;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens, CodeGenerationUtilities;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'phone', 'qr_code', 'password', 'country_code', 'image'
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

    public static function create(array $attributes = []){

        $user = new User;
        $attributes['qr_code'] = $user->timestamping($user->generateCode(5));
        $model = static::query()->create($attributes);

        $model->qr_code = $user->idstamping($model->qr_code, $model->id, true);
        $model->save();

        return $model;
    }

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
        $totalValid = 0;
        foreach ($this->transactions as $transaction) {

            $transactionPoints = $transaction->transactionPoints;
            if ($transactionPoints->is_valid) { $totalValid += $transactionPoints->available_points; }

        }
        // dump($totalValid);
        return $totalValid;
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

        $totalExpire = 0;
        foreach ($this->transactions as $transaction) {
            
            $transactionPoints = $transaction->transactionPoints;
            if ($transactionPoints->is_expired) { $totalExpire += $transactionPoints->available_points; }

        }
        return $totalExpire;
    }

    /**
     * Get the voucher instance status.
     *
     * @return bool
     */
    public function getLatestExpireAttribute()
    {
        $latestExpire = [];
        $transactions = $this->transactions()->orderBy('created_at')->get();
        foreach ($transactions as $transaction) {

            $transactionPoints = $transaction->transactionPoints;
            if ($transactionPoints->is_valid) { array_push($latestExpire, $transactionPoints); }

        }
        return collect($latestExpire);
    }

    /**
     * The roles that belong to the user.
     */
    public function roles()
    {
        return $this->belongsToMany('App\Models\Role');
    }

    /**
     * The notifications that belong to the user.
     */
    public function notifications()
    {
        return $this->belongsToMany('App\Models\Notification');
    }

    /**
     * Get the devices for the user.
     */
    public function devices()
    {
        return $this->hasMany('App\Models\Device');
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
