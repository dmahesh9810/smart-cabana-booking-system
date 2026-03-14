<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
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
            'booking_ref' => $this->booking_ref,
            'check_in' => $this->check_in,
            'check_out' => $this->check_out,
            'guests_count' => $this->guests_count,
            'total_amount' => $this->total_amount,
            'status' => $this->status,
            'cabana' => new CabanaResource($this->whenLoaded('cabana')),
            'has_review' => $this->review()->exists(),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
