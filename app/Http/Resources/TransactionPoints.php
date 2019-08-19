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
            'invoice_number' => $this->transaction->invoice_number,
            'original' => $this->original,
            'redeemed' => $this->redeemed,
            'available_points' => $this->available_points,
            'status' => config('constants.status.'.$this->status),
            'is_used' => $this->is_used,
            'is_refunded' => $this->is_refunded,
            'is_valid' => $this->is_valid,
            'is_pending' => $this->is_pending,
            'is_expired' => $this->is_expired,
            'pending_end_date' => $this->pending_end_date,
            'valid_end_date' => $this->valid_end_date,
            'used_at' => $this->used_at? $this->used_at->toDayDateTimeString(): null,
            'refunded_at' => $this->refunded_at? $this->refunded_at->toDayDateTimeString(): null,
            'created_at' => $this->created_at->toDayDateTimeString(),
        ];
    }
}
