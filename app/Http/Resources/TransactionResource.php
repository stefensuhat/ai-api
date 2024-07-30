<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'order_id' => $this->order_id,
            'grand_total' => $this->grand_total,
            'discount' => $this->discount,
            'tax' => $this->tax,
            'subtotal' => $this->subtotal,
            'status' => $this->status,
        ];
    }
}
