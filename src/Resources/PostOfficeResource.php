<?php

namespace PinIndia\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class PostOfficeResource extends JsonResource {
    public function toArray(Request $request): array {
        return [
            'name' => $this->name,
            'pincode' => $this->pincode->pincode,
            'distance' => $this->whenLoaded('distance', fn() => $this->distance),
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'district' => $this->pincode->district?->name ?? null,
            'state' => $this->pincode->district?->state?->name ?? null,
        ];
    }
}