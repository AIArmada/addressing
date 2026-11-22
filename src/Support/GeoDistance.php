<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Support;

use Illuminate\Support\Facades\DB;

/**
 * Shared bounding-box and spherical Haversine SQL helpers for latitude/longitude proximity queries.
 */
final class GeoDistance
{
    public const float EARTH_RADIUS_KM = 6371.0;

    /**
     * Compute a conservative latitude/longitude bounding box around a coordinate.
     *
     * @return array{min_lat: float, max_lat: float, min_lng: float, max_lng: float}
     */
    public static function boundingBox(float $latitude, float $longitude, float $radiusKm): array
    {
        $radiusKm = max(0.0, $radiusKm);
        $deltaLat = rad2deg($radiusKm / self::EARTH_RADIUS_KM);
        $minLat = max(-90.0, $latitude - $deltaLat);
        $maxLat = min(90.0, $latitude + $deltaLat);

        $cosLat = abs(cos(deg2rad($latitude)));

        if ($cosLat < 0.01) {
            return [
                'min_lat' => $minLat,
                'max_lat' => $maxLat,
                'min_lng' => -180.0,
                'max_lng' => 180.0,
            ];
        }

        $deltaLng = rad2deg($radiusKm / self::EARTH_RADIUS_KM / $cosLat);
        $rawMinLng = $longitude - $deltaLng;
        $rawMaxLng = $longitude + $deltaLng;

        if ($rawMinLng < -180.0 || $rawMaxLng > 180.0) {
            $minLng = -180.0;
            $maxLng = 180.0;
        } else {
            $minLng = $rawMinLng;
            $maxLng = $rawMaxLng;
        }

        return [
            'min_lat' => $minLat,
            'max_lat' => $maxLat,
            'min_lng' => $minLng,
            'max_lng' => $maxLng,
        ];
    }

    /**
     * Build a driver-safe spherical distance SQL expression (in kilometers) with acos input clamped to [-1, 1].
     */
    public static function haversineSql(string $latitudeExpression, string $longitudeExpression, ?string $driver = null): string
    {
        $resolvedDriver = $driver ?? (
            function_exists('app') && app()->bound('db')
                ? DB::connection()->getDriverName()
                : 'pgsql'
        );

        $cosExpression = sprintf(
            'cos(radians(?)) * cos(radians(%s)) * cos(radians(%s) - radians(?)) + sin(radians(?)) * sin(radians(%s))',
            $latitudeExpression,
            $longitudeExpression,
            $latitudeExpression,
        );

        $clamped = $resolvedDriver === 'sqlite'
            ? sprintf('min(1.0, max(-1.0, %s))', $cosExpression)
            : sprintf('least(1.0, greatest(-1.0, %s))', $cosExpression);

        return sprintf('(6371 * acos(%s))', $clamped);
    }

    /**
     * Ordered SQL parameter bindings matching {@see self::haversineSql()}.
     *
     * @return array{0: float, 1: float, 2: float}
     */
    public static function haversineBindings(float $latitude, float $longitude): array
    {
        return [$latitude, $longitude, $latitude];
    }

    /**
     * Compute great-circle distance in kilometers between two coordinates.
     */
    public static function kilometersBetween(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $latFrom = deg2rad($lat1);
        $latTo = deg2rad($lat2);
        $latDelta = deg2rad($lat2 - $lat1);
        $lngDelta = deg2rad($lng2 - $lng1);

        $angle = 2 * asin(min(1.0, sqrt(
            (sin($latDelta / 2) ** 2) +
            (cos($latFrom) * cos($latTo) * (sin($lngDelta / 2) ** 2)),
        )));

        return $angle * self::EARTH_RADIUS_KM;
    }
}
