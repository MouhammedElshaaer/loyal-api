<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{

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

    public function locale($locale){
        return $this->locales->where('locale', $locale);
    }

    public function value($locale, $field){
        return $this->locale($locale)->where('data_field', $field)->first()->value;
    }
}
