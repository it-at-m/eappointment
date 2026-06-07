<?php

/**
 * @package Zmsstatistic
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic\Service;

use BO\Zmsentities\Exchange;
use DateTimeImmutable;

class ReportCapacityService
{
    /** Fetch hour-level warehouse data up to this range length (chart buckets are derived in PHP). */
    private const MAX_HOURLY_FETCH_HOURS = 336;

    /**
     * Get exchange slot capacity data for period or custom date range.
     */
    public function getExchangeCapacityData(string $scopeId, ?array $dateRange, array $args): mixed
    {
        if ($scopeId === '') {
            return null;
        }

        if ($dateRange) {
            return $this->getExchangeCapacityForDateRange($scopeId, $dateRange);
        }

        if (isset($args['period'])) {
            return $this->getExchangeCapacityForPeriod($scopeId, $args['period']);
        }

        return null;
    }

    /**
     * Available date range per scope from warehouse subject list (periodstart / periodend).
     *
     * @return array<string, array{min: string, max: string}>
     */
    public function getScopeDateBoundsByScopeId(): array
    {
        try {
            $result = \App::$http->readGetResult('/warehouse/capacityscope/');
            if (!$result) {
                return [];
            }

            $subjectList = $result->getEntity();

            if (!$subjectList instanceof Exchange || empty($subjectList->data)) {
                return [];
            }

            $bounds = [];
            foreach ($subjectList->data as $row) {
                $scopeId = (string) ($row[0] ?? '');
                $periodStart = (string) ($row[1] ?? '');
                $periodEnd = (string) ($row[2] ?? '');

                if ($scopeId === '' || $periodStart === '' || $periodEnd === '') {
                    continue;
                }

                if (!isset($bounds[$scopeId])) {
                    $bounds[$scopeId] = [
                        'min' => $periodStart,
                        'max' => $periodEnd,
                    ];
                    continue;
                }

                $bounds[$scopeId]['min'] = min($bounds[$scopeId]['min'], $periodStart);
                $bounds[$scopeId]['max'] = max($bounds[$scopeId]['max'], $periodEnd);
            }

            return $bounds;
        } catch (\Throwable $exception) {
            return [];
        }
    }

    /**
     * Period list for navigation; derived from capacityscope report dates when API only returns "_".
     */
    public function getCapacityPeriod(string $scopeId): mixed
    {
        try {
            $result = \App::$http->readGetResult('/warehouse/capacityscope/' . $scopeId . '/');
            if (!$result) {
                return null;
            }

            $periodList = $result->getEntity();

            return $this->enrichPeriodList($periodList, $scopeId);
        } catch (\Throwable $exception) {
            return null;
        }
    }

    public function getExchangeCapacityForDateRange(string $scopeId, array $dateRange): mixed
    {
        if (!isset($dateRange['from'], $dateRange['to'])) {
            return null;
        }

        $exchange = $this->fetchAggregatedReport($scopeId, $dateRange, null);
        if (!$exchange || empty($exchange->data)) {
            return null;
        }

        $exchange->data = $this->filterRowsByBounds($exchange->data, $dateRange);

        return $this->finalizeExchange($exchange, $dateRange['from'], $dateRange['to']);
    }

    public function getExchangeCapacityForPeriod(string $scopeId, string $period): mixed
    {
        $exchange = $this->fetchAggregatedReport($scopeId, null, $period);
        if (!$exchange || empty($exchange->data)) {
            return null;
        }

        $bounds = $this->resolveTimelineBounds(null, $period);
        if ($bounds) {
            $exchange->data = $this->filterRowsByBounds($exchange->data, $bounds);
        }

        if (empty($exchange->data)) {
            return null;
        }

        return $this->finalizeExchange($exchange);
    }

    /**
     * Full timeline for the chart (zeros for closed hours/days). Table uses sparse API data.
     */
    public function buildChartExchange(Exchange $exchange, ?array $dateRange, ?string $period): Exchange
    {
        $chart = clone $exchange;
        $useHourlyTimeline = $this->shouldFetchHourlyFromApi($dateRange, $period);

        $chart->data = $this->fillMissingTimeline(
            $chart->data,
            $dateRange,
            $period,
            $useHourlyTimeline
        );

        $visualization = $chart['visualization'] ?? [];
        if (!is_array($visualization)) {
            $visualization = [];
        }
        $visualization['labelIntervalHours'] = $this->resolveChartLabelIntervalHours($dateRange, $period);
        $chart['visualization'] = $visualization;

        return $chart;
    }

    /**
     * X-axis tick label spacing in hours, or null for daily labels (data stays hourly/daily respectively).
     */
    public function resolveChartLabelIntervalHours(?array $dateRange, ?string $period): ?int
    {
        $hours = $this->resolveRangeDurationHours($dateRange, $period);
        if ($hours === null) {
            return null;
        }

        if ($hours <= 24) {
            return 1;
        }

        if ($hours <= 48) {
            return 2;
        }

        if ($hours <= 168) {
            return 6;
        }

        if ($hours <= self::MAX_HOURLY_FETCH_HOURS) {
            return 12;
        }

        return null;
    }

    public function shouldFetchHourlyFromApi(?array $dateRange, ?string $period): bool
    {
        $hours = $this->resolveRangeDurationHours($dateRange, $period);
        if ($hours !== null) {
            return $hours <= self::MAX_HOURLY_FETCH_HOURS;
        }

        if ($period && $period !== '_' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $period)) {
            return true;
        }

        return false;
    }

    /**
     * @return float|null Length of selected range in hours (inclusive end day).
     */
    public function resolveRangeDurationHours(?array $dateRange, ?string $period): ?float
    {
        $bounds = $this->resolveTimelineBounds($dateRange, $period);
        if (!$bounds) {
            return null;
        }

        $from = new DateTimeImmutable($bounds['from'] . ' 00:00:00');
        $to = new DateTimeImmutable($bounds['to'] . ' 23:59:59');

        return ($to->getTimestamp() - $from->getTimestamp()) / 3600;
    }

    private function fetchAggregatedReport(string $scopeId, ?array $dateRange, ?string $period): ?Exchange
    {
        try {
            $fetchHourly = $this->shouldFetchHourlyFromApi($dateRange, $period);
            $params = [];
            $urlPeriod = '_';

            if ($dateRange) {
                $params['fromDate'] = $dateRange['from'];
                $params['toDate'] = $dateRange['to'];
            }

            if ($fetchHourly) {
                $params['groupby'] = 'hour';
            }

            if ($period && $period !== '_') {
                $urlPeriod = $period;
            } elseif ($dateRange) {
                $urlPeriod = $dateRange['from'];
            }

            $result = \App::$http->readGetResult(
                '/warehouse/capacityscope/' . $scopeId . '/' . $urlPeriod . '/',
                $params === [] ? null : $params
            );
            if (!$result) {
                return null;
            }

            $exchange = $result->getEntity();
            if (!$exchange instanceof Exchange) {
                return null;
            }

            $sourceHourly = ($exchange->period ?? 'day') === 'hour';
            $exchange->data = $this->aggregateRowsByDate($exchange->data, $sourceHourly);

            return $exchange;
        } catch (\Throwable $exception) {
            return null;
        }
    }

    /**
     * Sum booked/planned counts per date (or hour) across multiple scopes.
     *
     * @param array<int, array<int, mixed>> $rows
     * @param bool $useHourlyKeys true = one row per clock hour, false = per calendar day
     * @return array<int, array<int, mixed>>
     */
    private function aggregateRowsByDate(array $rows, bool $useHourlyKeys): array
    {
        $byDate = [];

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $normalized = $this->normalizeDataRow($row, $useHourlyKeys);
            $key = $normalized[1];
            if ($key === '') {
                continue;
            }

            if (!isset($byDate[$key])) {
                $byDate[$key] = $normalized;
                continue;
            }

            $byDate[$key][2] += $normalized[2];
            $byDate[$key][3] += $normalized[3];
        }

        ksort($byDate);

        return array_values($byDate);
    }

    /**
     * Insert zero rows for each hour or day in the selected range so the chart shows
     * closed periods and gaps between days (not only timestamps with slot data).
     *
     * @param array<int, array<int, mixed>> $rows
     * @return array<int, array<int, mixed>>
     */
    private function fillMissingTimeline(
        array $rows,
        ?array $dateRange,
        ?string $period,
        bool $useHourlyTimeline
    ): array {
        $bounds = $this->resolveTimelineBounds($dateRange, $period);
        if (!$bounds) {
            return $rows;
        }

        $byKey = [];
        $subjectId = '';

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $normalized = $this->normalizeDataRow($row, $useHourlyTimeline);
            $key = $normalized[1];
            if ($key === '') {
                continue;
            }

            $byKey[$key] = $normalized;
            if ($subjectId === '' && $normalized[0] !== '') {
                $subjectId = $normalized[0];
            }
        }

        $from = $bounds['from'];
        $to = $bounds['to'];
        $filled = [];

        if ($useHourlyTimeline) {
            $start = new DateTimeImmutable($from . ' 00:00:00');
            $end = new DateTimeImmutable($to . ' 23:00:00');
            $cursor = $start;

            while ($cursor <= $end) {
                $key = $this->normalizeTimelineKey(
                    $cursor->format('Y-m-d') . ' ' . $cursor->format('H') . ':00',
                    true
                );
                $filled[] = $byKey[$key] ?? [$subjectId, $key, 0, 0];
                $cursor = $cursor->modify('+1 hour');
            }

            return $filled;
        }

        $start = new DateTimeImmutable($from);
        $end = new DateTimeImmutable($to);
        $cursor = $start;

        while ($cursor <= $end) {
            $key = $cursor->format('Y-m-d');
            $filled[] = $byKey[$key] ?? [$subjectId, $key, 0, 0];
            $cursor = $cursor->modify('+1 day');
        }

        return $filled;
    }

    private function rowDateValue(array $row): string
    {
        if (isset($row['date'])) {
            return (string) $row['date'];
        }

        return (string) ($row[1] ?? '');
    }

    private function rowNumericValue(array $row, string $variable, int $position): int
    {
        if (isset($row[$variable])) {
            return (int) $row[$variable];
        }

        return (int) ($row[$position] ?? 0);
    }

    /**
     * @param array<int|string, mixed> $row
     * @return array{0: string, 1: string, 2: int, 3: int}
     */
    private function normalizeDataRow(array $row, bool $useHourlyKeys): array
    {
        $date = $this->rowDateValue($row);

        return [
            (string) ($row['subjectid'] ?? $row[0] ?? ''),
            $this->normalizeTimelineKey($date, $useHourlyKeys),
            $this->rowNumericValue($row, 'bookedcount', 2),
            $this->rowNumericValue($row, 'plannedcount', 3),
        ];
    }

    private function normalizeTimelineKey(string $date, bool $useHourlyKeys): string
    {
        if ($date === '') {
            return '';
        }

        if (!$useHourlyKeys) {
            return substr($date, 0, 10);
        }

        $timestamp = strtotime($date);
        if ($timestamp === false) {
            return $date;
        }

        return date('Y-m-d H', $timestamp) . ':00';
    }

    /**
     * @return array{from: string, to: string}|null
     */
    private function resolveTimelineBounds(?array $dateRange, ?string $period): ?array
    {
        if ($dateRange && isset($dateRange['from'], $dateRange['to'])) {
            return [
                'from' => $dateRange['from'],
                'to' => $dateRange['to'],
            ];
        }

        if (!$period || $period === '_') {
            return null;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $period)) {
            return ['from' => $period, 'to' => $period];
        }

        if (preg_match('/^\d{4}-\d{2}$/', $period)) {
            $start = new DateTimeImmutable($period . '-01');
            $end = $start->modify('last day of this month');

            return [
                'from' => $start->format('Y-m-d'),
                'to' => $end->format('Y-m-d'),
            ];
        }

        if (preg_match('/^\d{4}$/', $period)) {
            return [
                'from' => $period . '-01-01',
                'to' => $period . '-12-31',
            ];
        }

        return null;
    }

    /**
     * @param array<int, array<int, mixed>> $rows
     * @param array{from: string, to: string} $bounds
     * @return array<int, array<int, mixed>>
     */
    private function filterRowsByBounds(array $rows, array $bounds): array
    {
        $from = $bounds['from'];
        $to = $bounds['to'];

        return array_values(array_filter($rows, static function ($row) use ($from, $to) {
            $date = (string) ($row[1] ?? '');
            if ($date === '') {
                return false;
            }

            $day = substr($date, 0, 10);

            return $day >= $from && $day <= $to;
        }));
    }

    private function finalizeExchange(Exchange $exchange, ?string $fromDate = null, ?string $toDate = null): Exchange
    {
        if ($fromDate && $toDate) {
            $exchange->firstDay = $this->dayFromString($fromDate);
            $exchange->lastDay = $this->dayFromString($toDate);
        } elseif (!empty($exchange->data)) {
            $first = (string) $exchange->data[0][1];
            $last = (string) $exchange->data[count($exchange->data) - 1][1];
            $exchange->firstDay = $this->dayFromString(substr($first, 0, 10));
            $exchange->lastDay = $this->dayFromString(substr($last, 0, 10));
        }

        return $exchange;
    }

    private function dayFromString(string $date): \BO\Zmsentities\Day
    {
        $dateTime = new \DateTime(substr($date, 0, 10));

        return (new \BO\Zmsentities\Day())->setDateTime($dateTime);
    }

    private function enrichPeriodList(mixed $periodList, string $scopeId): mixed
    {
        if (!$periodList instanceof Exchange) {
            return $periodList;
        }

        $hasOnlyAggregatePeriod = count($periodList->data) === 1
            && isset($periodList->data[0][0])
            && $periodList->data[0][0] === '_';

        if (!$hasOnlyAggregatePeriod) {
            return $periodList;
        }

        $exchange = $this->fetchAggregatedReport($scopeId, null, null);
        if (!$exchange || empty($exchange->data)) {
            return $periodList;
        }

        $years = [];
        $months = [];

        foreach ($exchange->data as $row) {
            $date = (string) ($row[1] ?? '');
            if ($date === '') {
                continue;
            }

            $year = substr($date, 0, 4);
            $month = substr($date, 0, 7);

            if (!in_array($year, $years, true)) {
                $years[] = $year;
            }

            if (!in_array($month, $months, true)) {
                $months[] = $month;
            }
        }

        rsort($years);
        rsort($months);

        $periodList->data = [];
        foreach ($years as $year) {
            $periodList->data[] = [$year];
        }
        foreach ($months as $month) {
            $periodList->data[] = [$month];
        }

        return $periodList;
    }
}
