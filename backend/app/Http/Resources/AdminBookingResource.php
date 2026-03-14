<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminBookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'booking_ref'    => $this->booking_ref,
            'status'         => $this->status,
            'total_amount'   => $this->total_amount,
            'guests_count'   => $this->guests_count,
            'check_in'       => $this->check_in,
            'check_out'      => $this->check_out,
            'created_at'     => $this->created_at?->toDateString(),

            // User info
            'user'           => $this->whenLoaded('user', fn() => [
                'id'    => $this->user->id,
                'name'  => $this->user->name,
                'email' => $this->user->email,
            ]),

            // Cabana info
            'cabana'         => $this->whenLoaded('cabana', fn() => [
                'id'       => $this->cabana->id,
                'name'     => $this->cabana->name,
                'location' => $this->cabana->location,
            ]),

            // Payment info (latest payment record)
            'payment_status' => $this->whenLoaded(
                'payment',
                fn() => $this->payment?->payment_status ?? 'pending',
                'pending'
            ),
            'payment_amount' => $this->whenLoaded(
                'payment',
                fn() => $this->payment?->amount,
            ),
        ];
    }
}
