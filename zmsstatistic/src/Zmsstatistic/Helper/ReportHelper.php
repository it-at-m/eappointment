<?php

namespace BO\Zmsstatistic\Helper;

class ReportHelper
{
    public static function withMaxAndAverage($entity, $targetKey)
    {
        foreach ($entity->data as $date => $dateItems) {
            $maxima = 0;
            $total = 0;
            $count = 0;
            foreach ($dateItems as $hourItems) {
                if (is_array($hourItems)) { // Check if $hourItems is an array
                    foreach ($hourItems as $key => $value) {
                        if (is_numeric($value) && $targetKey == $key && 0 < $value) {
                            $total += $value;
                            $count += 1;
                            $maxima = ($maxima > $value) ? $maxima : $value;
                        }
                    }
                }
            }
            $entity->data[$date]['max_' . $targetKey] = $maxima;
            $entity->data[$date]['average_' . $targetKey] = (! $total || ! $count) ? 0 : $total / $count;
        }
        return $entity;
    }

    public static function formatTimeValue($value)
    {
        if (!is_numeric($value)) {
            return $value;
        }
        $minutes = floor($value);
        $seconds = round(($value - $minutes) * 60);
        if ($seconds >= 60) {
            $minutes += 1;
            $seconds = 0;
        }
        return sprintf('%02d:%02d', $minutes, $seconds);
    }
}
