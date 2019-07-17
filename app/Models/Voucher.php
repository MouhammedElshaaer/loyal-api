<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Http\Traits\LocaleUtilities;

class Voucher extends Model
{
    use LocaleUtilities;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'instances',
        'deactivated',
        'created_at',
        'updated_at'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'points', 'title', 'description', 'image', 'deactivated'
    ];

    public function locales(){
        return $this->morphMany('App\Models\Translation', 'dataRow', 'data_type', 'data_row_id');
    }

}
