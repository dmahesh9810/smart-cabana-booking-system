<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminBookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'booking_ref' => $this->booking_ref,
            'customer_name' => $this->whenLoaded('user', fn() => $this->user->name),
            'cabana_name' => $this->whenLoaded('cabana', fn() => $this->cabana->name),
            'check_in' => $this->check_in,
            'check_out' => $this->check_out,
            'status' => $this->status,
            'payment_status' => $this->whenLoaded('payment', fn() => $this->payment->status, 'pending'),
        ];
    }
}
