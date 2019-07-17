<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    /**
     * Get the owning imageable model.
     */
    public function dataRow()
    {
        return $this->morphTo();
    }
}
