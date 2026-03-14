<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CabanaResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'price_per_night' => $this->price_per_night,
            'max_guests' => $this->max_guests,
            'location' => $this->location,
            'is_active' => $this->is_active,
            'primary_image' => $this->primaryImage,
            'images' => $this->images,
            'amenities' => $this->amenities,
            'reviews_count' => $this->reviews()->count(),
            'average_rating' => $this->reviews()->avg('rating') ?? 0,
            'reviews' => ReviewResource::collection($this->whenLoaded('reviews')),
        ];
    }
}
