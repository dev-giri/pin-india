<?php

namespace PinIndia\Services;

use PinIndia\Models\Pincode;
use PinIndia\Models\PostOffice;
use PinIndia\Resources\PostOfficeResource;
use PinIndia\Resources\PostOfficeDistanceResource;

class PinIndiaService
{
    private string $prefix;

    public function __construct() {
        $this->prefix = config('pinindia.table_prefix') ? config('pinindia.table_prefix') . '_' : '';
    }

    public function findByPincode(int $pincode)
    {
        $postOffices = Pincode::with('postOffices', 'district.state')
            ->where('pincode', $pincode)
            ->firstOrFail()
            ->postOffices;
        return PostOfficeResource::collection($postOffices);
    }

    public function findByPostOffice(string $name, int $limit = 10)
    {
        $postOffices = PostOffice::with('pincode.district.state')
                         ->where('name', 'like', "%$name%")
                         ->orWhere('office', 'like', "%$name%")
                         ->orderBy('name')
                         ->orderBy('office')
                         ->limit($limit)
                         ->get();
        return PostOfficeResource::collection($postOffices);
    }

    public function getNearestByCoordinates(float $latitude, float $longitude, int $radiusInKm = 10){
        $postOffices = PostOffice::with('pincode.district.state')
            ->selectRaw("
                {$this->prefix}post_offices.*, 
                (6371 * acos(
                    cos(radians(?)) * 
                    cos(radians(latitude)) * 
                    cos(radians(longitude) - radians(?)) + 
                    sin(radians(?)) * 
                    sin(radians(latitude))
                )) AS distance", [$latitude, $longitude, $latitude])
            ->having('distance', '<=', $radiusInKm)
            ->orderBy('distance')
            ->get();
        return PostOfficeDistanceResource::collection($postOffices);
    }

    public function getNearestByPincode(int $pincode, int $radiusInKm = 10)
    {
        $referenceOffice = PostOffice::whereHas('pincode', function ($query) use ($pincode) {
            $query->where('pincode', $pincode);
        })->whereNotNull('latitude')->whereNotNull('longitude')->first();

        if (!$referenceOffice) {
            return collect(); // Return empty if no coordinates
        }

        return $this->getNearestByCoordinates($referenceOffice->latitude, $referenceOffice->longitude, $radiusInKm);
    }

    public function getNearestByPostOffice(string $officeName, int $radiusInKm = 10)
    {
        $referenceOffice = PostOffice::where('name', 'like', "%$officeName%")
            ->orWhere('office', 'like', "%$officeName%")
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->first();

        if (!$referenceOffice) {
            return collect(); // Return empty if no coordinates
        }

        return $this->getNearestByCoordinates($referenceOffice->latitude, $referenceOffice->longitude, $radiusInKm);
    }
}
