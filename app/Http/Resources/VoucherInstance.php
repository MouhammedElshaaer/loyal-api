<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VoucherInstance extends JsonResource
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
            'user_id' => $this->user_id,
            'qr_code' => $this->qr_code,
            'title' => $this->voucher->title,
            'used_at' => $this->used_at? $this->used_at->toDayDateTimeString(): null,
            'invoice_number' => $this->transaction_id? $this->transaction->invoice_number: null,
            'valid_end_date' => $this->valid_end_date,
            'status' => $this->status,
            'is_used' => $this->is_used,
            'is_valid' => $this->is_valid,
            'is_expired' => $this->is_expired,
            'created_at' => $this->created_at->toDayDateTimeString(),
        ];
    }
}
