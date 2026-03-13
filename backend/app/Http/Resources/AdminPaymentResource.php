<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminPaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'payhere_payment_id' => $this->payhere_payment_id,
            'booking_ref' => $this->whenLoaded('booking', fn() => $this->booking->booking_ref),
            'amount' => $this->amount,
            'currency' => $this->currency,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
