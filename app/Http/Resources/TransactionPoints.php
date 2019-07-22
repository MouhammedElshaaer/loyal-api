<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TransactionPoints extends JsonResource
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
            'id' => $this->id,
            'original' => $this->original,
            'redeemed' => $this->redeemed,
            'available_points' => $this->available_points,
            'status' => $this->status,
            'is_used' => $this->is_used,
            'is_refunded' => $this->is_refunded,
            'is_valid' => $this->is_valid,
            'is_pending' => $this->is_pending,
            'is_expired' => $this->is_expired,
            'pending_end_date' => $this->pending_end_date,
            'valid_end_date' => $this->valid_end_date,
            'used_at' => $this->used_at,
            'refunded_at' => $this->refunded_at,
            'created_at' => $this->created_at,
        ];
    }
}
