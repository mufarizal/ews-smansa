<?php

namespace App\Helpers;

class LocationHelper
{
    /**
     * Haversine formula untuk calculate jarak antara 2 koordinat (dalam meter)
     */
    public static function getDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earth_radius = 6371000; // dalam meter

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * asin(sqrt($a));
        $distance = $earth_radius * $c;

        return round($distance, 2); // dalam meter
    }

    /**
     * Get distance from school
     */
    public static function getDistanceFromSchool($latitude, $longitude)
    {
        $schoolLat = config('sekolah.latitude');
        $schoolLon = config('sekolah.longitude');

        return self::getDistance($schoolLat, $schoolLon, $latitude, $longitude);
    }

    /**
     * Check jika siswa dalam radius school
     */
    public static function isWithinSchoolRadius($latitude, $longitude)
    {
        $distance = self::getDistanceFromSchool($latitude, $longitude);
        $maxDistance = config('sekolah.max_distance_meter', 500);

        return $distance <= $maxDistance;
    }

    /**
     * Format distance dengan unit
     */
    public static function formatDistance($meters)
    {
        if ($meters < 1000) {
            return round($meters) . ' m';
        }
        return round($meters / 1000, 2) . ' km';
    }
}
