<?php

namespace App\Http\Traits;

use Illuminate\Http\Exceptions\HttpResponseException;

use Carbon\Carbon;

trait CodeGenerationUtilities
{
    protected function generateCode($len){
        $code = '';
        for($i = 0; $i < $len; $i++) {$code .= mt_rand(0, 9);}
        return $code;
    }

    public function timestamping($code, $prefixed = false){
        return $prefixed? Carbon::now()->timestamp.$code: $code.Carbon::now()->timestamp;
    }

    public function idstamping($code, $id, $prefixed = false){
        return $prefixed? $id.$code: $code.$id;
    }
}