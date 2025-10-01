<?php

namespace BO\Zmsstatistic\Helper;

use DateTime;

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

    /**
     * Extract selected scope IDs from request parameters
     */
    public function extractSelectedScopes(array $scopes): array
    {
        if (!empty($scopes)) {
            $validScopes = array_filter($scopes, function ($scopeId) {
                return is_numeric($scopeId) && $scopeId > 0;
            });

            if (!empty($validScopes)) {
                return array_map('intval', $validScopes);
            }
        }

        return [];
    }

    /**
     * Extract and validate date range from request parameters
     */
    public function extractDateRange(?string $fromDate, ?string $toDate): ?array
    {
        if ($fromDate && $toDate && $this->isValidDateFormat($fromDate) && $this->isValidDateFormat($toDate)) {
            return [
                'from' => $fromDate,
                'to' => $toDate
            ];
        }

        return null;
    }

    /**
     * Validate if the given string is a valid date format (YYYY-MM-DD)
     */
    public function isValidDateFormat(string $date): bool
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return false;
        }

        $dateTime = DateTime::createFromFormat('Y-m-d', $date);
        return $dateTime && $dateTime->format('Y-m-d') === $date;
    }

    /**
     * Get all years that need to be fetched for a date range
     */
    public function getYearsForDateRange(string $fromDate, string $toDate): array
    {
        $fromYear = (int) substr($fromDate, 0, 4);
        $toYear = (int) substr($toDate, 0, 4);

        $years = [];
        for ($year = $fromYear; $year <= $toYear; $year++) {
            $years[] = $year;
        }

        return $years;
    }
}
