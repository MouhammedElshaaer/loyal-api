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
        $points = $this->value($locale, 'points');
        $title = $this->value($locale, 'title');
        $description = $this->value($locale, 'description');
        $image = $this->value($locale, 'image');

        return [
            'id' => $this->id,
            'points' => $this->when($points, $points, $this->points),
            'title' => $this->when($title, $title, $this->title),
            'description' => $this->when($description, $description, $this->description),
            'image' => $this->when($image, $image, $this->image)
        ];
    }
}
