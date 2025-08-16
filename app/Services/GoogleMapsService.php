<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Arr;

class GoogleMapsService
{
    protected string $key;
    public function __construct() { $this->key = config('services.google_maps.key'); }

    public function reverseGeocode(float $lat, float $lng): array {
        $res = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
            'latlng' => "{$lat},{$lng}",
            'key' => $this->key,
        ])->json();

        $first = $res['results'][0] ?? null;
        return [
            'formatted_address' => $first['formatted_address'] ?? null,
            'place_id' => $first['place_id'] ?? null,
            'components' => $first['address_components'] ?? [],
        ];
    }

    public function placeDetails(string $placeId): array {
        $res = Http::get('https://maps.googleapis.com/maps/api/place/details/json', [
            'place_id' => $placeId,
            'fields'   => 'geometry/location,formatted_address,place_id',
            'key'      => $this->key,
        ])->json();

        $r = $res['result'] ?? [];
        return [
            'lat' => Arr::get($r, 'geometry.location.lat'),
            'lng' => Arr::get($r, 'geometry.location.lng'),
            'formatted_address' => $r['formatted_address'] ?? null,
            'place_id' => $r['place_id'] ?? null,
        ];
    }
}
