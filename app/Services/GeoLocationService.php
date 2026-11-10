<?php

namespace App\Services;


class GeoLocationService
{
//     // calculate distance
//     public function calculateDistancePolyline($points = [])
//     {
//         if (!$this->isStrict2DArray($points)) {
//             return false;
//         }

//         $totalDistance = 0;
//         $polyline = new Polyline();
//         foreach ($points as $key => $value) {
//             $polyline->addPoint(
//                 new Coordinate($value[0], $value[1])
//             );
//         }

//         $totalDistance = $polyline->getLength(new Vincenty());
//         // dd($totalDistance);
//         return $totalDistance;
//     }

//     function isStrict2DArray(array $array): bool
//     {
//         return !empty($array)
//             && count($array) === count(array_filter($array, 'is_array'));
//     }


    public function formatDistance(?float $distance): string
    {
        if ($distance === null) {
            return '-';
        }

        return $distance >= 1000
            ? round($distance / 1000, 2) . ' km'
            : round($distance) . ' m';
    }
}
