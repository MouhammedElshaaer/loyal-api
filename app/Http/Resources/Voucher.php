<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Voucher extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $locale = \App::getLocale();
        return [
            'id' => $this->id,
            'points' => $this->value($locale, 'points'),
            'title' => $this->value($locale, 'title'),
            'description' => $this->value($locale, 'description'),
            'image' => $this->value($locale, 'image')
        ];
    }
}
