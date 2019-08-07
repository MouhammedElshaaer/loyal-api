<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class User extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'=> $this->id,
            'email'=> $this->email,
            'qr_code'=> $this->qr_code,
            'country_code'=>$this->country_code,
            'phone'=> $this->phone,
            'name'=> $this->name,
            'image'=> $this->image,
            'verified'=> $this->verified,
            'deactivated'=> $this->deactivated
        ];
    }
}
