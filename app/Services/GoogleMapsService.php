<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GoogleMapsService
{
    public function geocode(string $address): ?array
    {
        $response = Http::get(config('services.google_maps.geocode_url'), [
            'address' => $address,
            'key' => config('services.google_maps.api_key'),
        ]);

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json();

        if (($data['status'] ?? null) !== 'OK' || empty($data['results'][0]['geometry']['location'])) {
            return null;
        }

        $location = $data['results'][0]['geometry']['location'];

        return [
            'latitude' => $location['lat'],
            'longitude' => $location['lng'],
        ];
    }

    public function distanceMatrix(
        float $originLat,
        float $originLng,
        float $destinationLat,
        float $destinationLng
    ): ?array {
        $response = Http::get(config('services.google_maps.distance_url'), [
            'origins' => $originLat . ',' . $originLng,
            'destinations' => $destinationLat . ',' . $destinationLng,
            'key' => config('services.google_maps.api_key'),
        ]);

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json();

        $element = $data['rows'][0]['elements'][0] ?? null;

        if (! $element || ($element['status'] ?? null) !== 'OK') {
            return null;
        }

        $distanceMeters = $element['distance']['value'] ?? 0;
        $durationSeconds = $element['duration']['value'] ?? 0;

        return [
            'distance_km' => round($distanceMeters / 1000, 2),
            'duration_minutes' => (int) ceil($durationSeconds / 60),
        ];
    }

    public function buildGoogleMapsUrl(float $lat, float $lng): string
    {
        return 'https://www.google.com/maps/dir/?api=1&destination=' . $lat . ',' . $lng;
    }
}