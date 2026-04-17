<?php

namespace App\Services;

class DistanceService
{
    public function haversine(
        float $lat1,
        float $lon1,
        float $lat2,
        float $lon2
    ): float {
        $earthRadius = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c, 2);
    }

    public function estimateMinutes(float $distanceKm, float $avgSpeedKmPerHour = 30): int
    {
        if ($avgSpeedKmPerHour <= 0) {
            return 0;
        }

        return (int) ceil(($distanceKm / $avgSpeedKmPerHour) * 60);
    }

    public function buildGoogleMapsUrl(float $lat, float $lng): string
    {
        return 'https://www.google.com/maps/dir/?api=1&destination=' . $lat . ',' . $lng;
    }
}