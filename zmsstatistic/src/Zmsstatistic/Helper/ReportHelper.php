<?php

namespace BO\Zmsstatistic\Helper;

use DateTime;
use DateTimeImmutable;

class ReportHelper
{
    public static function withMaxAndAverage(mixed $entity, string $targetKey): mixed
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

    public static function withTotalCustomers(mixed $entity): mixed
    {
        foreach ($entity->data as $dateKey => $dateItems) {
            if (!is_array($dateItems)) {
                continue;
            }

            foreach ($dateItems as $hour => $hourItems) {
                if (!is_array($hourItems)) {
                    continue;
                }

                $countSpontan = (int) ($hourItems['waitingcount'] ?? 0);
                $countTermin  = (int) ($hourItems['waitingcount_termin'] ?? 0);
                $countTotal   = $countSpontan + $countTermin;

                $waitSpontan = (float) ($hourItems['waitingtime'] ?? 0);
                $waitTermin  = (float) ($hourItems['waitingtime_termin'] ?? 0);

                $waySpontan  = (float) ($hourItems['waytime'] ?? 0);
                $wayTermin   = (float) ($hourItems['waytime_termin'] ?? 0);

                $entity->data[$dateKey][$hour]['waitingcount_total'] = $countTotal;

                $entity->data[$dateKey][$hour]['waitingtime_total'] = ($countTotal > 0)
                    ? (($waitSpontan * $countSpontan) + ($waitTermin * $countTermin)) / $countTotal
                    : 0;

                $entity->data[$dateKey][$hour]['waytime_total'] = ($countTotal > 0)
                    ? (($waySpontan * $countSpontan) + ($wayTermin * $countTermin)) / $countTotal
                    : 0;
            }
        }

        return $entity;
    }

    public static function withGlobalMaxAndAverage(mixed $entity, string $targetKey): mixed
    {
        $maxima = 0;
        $total  = 0;
        $count  = 0;

        foreach ($entity->data as $dateItems) {
            if (!is_array($dateItems)) {
                continue;
            }
            foreach ($dateItems as $hourItems) {
                if (!is_array($hourItems)) {
                    continue;
                }
                $value = $hourItems[$targetKey] ?? null;
                if (is_numeric($value) && $value > 0) {
                    $value  = (float) $value;
                    $maxima = ($maxima > $value) ? $maxima : $value;
                    $total += $value;
                    $count++;
                }
            }
        }

        $average = ($count > 0) ? ($total / $count) : 0;

        if (is_object($entity->data)) {
            if (!isset($entity->data->max) || !is_array($entity->data->max)) {
                $entity->data->max = [];
            }
            $entity->data->max['max_' . $targetKey] = $maxima;
            $entity->data->max['average_' . $targetKey] = $average;
        } elseif (is_array($entity->data)) {
            if (!isset($entity->data['max']) || !is_array($entity->data['max'])) {
                $entity->data['max'] = [];
            }
            $entity->data['max']['max_' . $targetKey] = $maxima;
            $entity->data['max']['average_' . $targetKey] = $average;
        }

        return $entity;
    }

    /**
     * Format minutes as mm:ss, rounding the full duration to the nearest second.
     * Same rule as the Twig formatMinutesToTime macro, including its string cast
     * via |replace, which changes some .5-second floats (e.g. 3:27 vs 3:28).
     */
    public static function formatTimeValue(mixed $value): mixed
    {
        if (!is_numeric($value)) {
            return $value;
        }
        $totalMinutes = (float) str_replace(',', '.', (string) $value);
        $totalSeconds = (int) round($totalMinutes * 60);
        if ($totalSeconds <= 0) {
            return '00:00';
        }

        return sprintf('%02d:%02d', intdiv($totalSeconds, 60), $totalSeconds % 60);
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
     * Workstation scope id when the user has selected a default location, otherwise null.
     */
    public function getWorkstationScopeId(mixed $workstation): ?int
    {
        $scopeId = (int) ($workstation->scope['id'] ?? 0);

        return $scopeId > 0 ? $scopeId : null;
    }

    /**
     * Scope id(s) for warehouse report queries from form selection or workstation default.
     */
    public function resolveScopeIdParam(array $selectedScopes, ?int $workstationScopeId): string
    {
        if (!empty($selectedScopes)) {
            return implode(',', $selectedScopes);
        }

        return $workstationScopeId !== null ? (string) $workstationScopeId : '';
    }

    /**
     * Extract and validate date range from request parameters
     */
    public function extractDateRange(?string $fromDate, ?string $toDate): ?array
    {
        if (
            self::hasText($fromDate) && self::hasText($toDate)
            && $this->isValidDateFormat($fromDate) && $this->isValidDateFormat($toDate)
        ) {
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
    public function getYearDateBounds(int|string $year, string $fromDate, string $toDate): ?array
    {
        $requestedFrom = new DateTimeImmutable($fromDate);
        $requestedTo = new DateTimeImmutable($toDate);
        $yearStart = new DateTimeImmutable($year . '-01-01');
        $yearEnd = new DateTimeImmutable($year . '-12-31');

        $yearFrom = $requestedFrom > $yearStart ? $requestedFrom : $yearStart;
        $yearTo = $requestedTo < $yearEnd ? $requestedTo : $yearEnd;

        if ($yearFrom > $yearTo) {
            return null;
        }

        return [
            'from' => $yearFrom->format('Y-m-d'),
            'to' => $yearTo->format('Y-m-d'),
        ];
    }

    /**
     * @psalm-assert-if-true non-empty-array $value
     */
    public static function hasValues(?array $value): bool
    {
        return $value !== null && $value !== [];
    }

    /**
     * @psalm-assert-if-true non-empty-string $value
     */
    public static function hasText(?string $value): bool
    {
        return $value !== null && $value !== '';
    }

    /**
     * @psalm-assert-if-true non-empty-string $period
     */
    public static function hasNamedPeriod(?string $period): bool
    {
        return $period !== null && $period !== '' && $period !== '_';
    }
}
