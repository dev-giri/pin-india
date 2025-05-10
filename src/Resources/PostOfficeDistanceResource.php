<?php

namespace PinIndia\Resources;

use Illuminate\Http\Request;

class PostOfficeDistanceResource extends PostOfficeResource {
    public function toArray(Request $request): array {
        return [
            ...parent::toArray($request),
            'distance' => $this->distance,
        ];
    }
}
