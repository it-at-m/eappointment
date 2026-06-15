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

    /** Above this count, slot-time hints group by duration instead of listing scope names. */
    private const SLOT_TIME_HINT_MAX_NAMED_SCOPES = 4;

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
     * @param array<int, string|int> $scopeIds
     * @return array<int, array{id: string, name: string, slotTimeInMinutes: ?int}>
     */
    public function getSelectedScopeSlotTimes(array $scopeIds): array
    {
        if ($scopeIds === []) {
            return [];
        }

        try {
            $result = \App::$http->readGetResult('/scope/');
            if (!$result) {
                return [];
            }

            $scopeList = $result->getData();
            if (!is_array($scopeList) && !($scopeList instanceof \Traversable)) {
                return [];
            }

            $scopeById = [];
            foreach ($scopeList as $scope) {
                $id = (string) ($scope->id ?? '');
                if ($id !== '') {
                    $scopeById[$id] = $scope;
                }
            }

            $items = [];
            foreach ($scopeIds as $scopeId) {
                $id = (string) $scopeId;
                if (!isset($scopeById[$id])) {
                    continue;
                }

                $scope = $scopeById[$id];
                $items[] = [
                    'id' => $id,
                    'name' => $this->resolveScopeDisplayName($scope, $id),
                    'slotTimeInMinutes' => $this->resolveScopeSlotTimeMinutes($scope),
                ];
            }

            return $items;
        } catch (\Throwable $exception) {
            return [];
        }
    }

    /**
     * @param array<int, array{id: string, name: string, slotTimeInMinutes: ?int}> $scopeSlotTimes
     */
    public function formatScopeSlotTimeHint(array $scopeSlotTimes): ?string
    {
        if ($scopeSlotTimes === []) {
            return null;
        }

        $knownTimes = array_values(array_filter(
            array_map(
                static fn (array $item): ?int => $item['slotTimeInMinutes'] ?? null,
                $scopeSlotTimes
            ),
            static fn (?int $minutes): bool => $minutes !== null
        ));

        if ($knownTimes === []) {
            return null;
        }

        if (count($scopeSlotTimes) === 1) {
            return sprintf(
                'Zeitschlitzdauer laut Öffnungszeit: %d Min.',
                $knownTimes[0]
            );
        }

        $uniqueTimes = array_values(array_unique($knownTimes));
        if (count($uniqueTimes) === 1 && count($knownTimes) === count($scopeSlotTimes)) {
            return sprintf(
                'Zeitschlitzdauer laut Öffnungszeit: %d Min. (alle ausgewählten Standorte)',
                $uniqueTimes[0]
            );
        }

        if (count($scopeSlotTimes) > self::SLOT_TIME_HINT_MAX_NAMED_SCOPES) {
            return $this->formatGroupedScopeSlotTimeHint($scopeSlotTimes);
        }

        $parts = [];
        foreach ($scopeSlotTimes as $item) {
            if (($item['slotTimeInMinutes'] ?? null) === null) {
                continue;
            }

            $parts[] = sprintf(
                '%s: %d Min.',
                $item['name'],
                $item['slotTimeInMinutes']
            );
        }

        return $parts === []
            ? null
            : 'Zeitschlitzdauer laut Öffnungszeit: ' . implode('; ', $parts);
    }

    /**
     * @param array<int, array{id: string, name: string, slotTimeInMinutes: ?int}> $scopeSlotTimes
     */
    private function formatGroupedScopeSlotTimeHint(array $scopeSlotTimes): ?string
    {
        $byMinutes = [];
        foreach ($scopeSlotTimes as $item) {
            $minutes = $item['slotTimeInMinutes'] ?? null;
            if ($minutes === null) {
                continue;
            }

            $byMinutes[$minutes] = ($byMinutes[$minutes] ?? 0) + 1;
        }

        if ($byMinutes === []) {
            return null;
        }

        ksort($byMinutes, SORT_NUMERIC);

        $parts = [];
        foreach ($byMinutes as $minutes => $count) {
            $parts[] = sprintf(
                '%d Min. (%d %s)',
                $minutes,
                $count,
                $count === 1 ? 'Standort' : 'Standorte'
            );
        }

        return 'Zeitschlitzdauer laut Öffnungszeit: ' . implode(', ', $parts);
    }

    private function resolveScopeDisplayName(mixed $scope, string $id): string
    {
        if (!is_object($scope)) {
            return 'Standort ' . $id;
        }

        $contactName = '';
        if (isset($scope->contact)) {
            $contactName = (string) ($scope->contact->name ?? '');
        }

        $shortName = (string) ($scope->shortName ?? '');
        $name = trim($contactName . ' ' . $shortName);

        return $name !== '' ? $name : 'Standort ' . $id;
    }

    private function resolveScopeSlotTimeMinutes(mixed $scope): ?int
    {
        if (!is_object($scope) || !isset($scope->provider) || !is_object($scope->provider)) {
            return null;
        }

        if (!method_exists($scope->provider, 'getSlotTimeInMinutes')) {
            return null;
        }

        $slotTime = $scope->provider->getSlotTimeInMinutes();

        return $slotTime !== null ? (int) $slotTime : null;
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
     * Sparse API rows for the chart (same as legacy warehouse reports).
     */
    public function buildSparseChartExchange(Exchange $exchange, ?array $dateRange, ?string $period): Exchange
    {
        return $this->applyChartVisualizationSettings(clone $exchange, $dateRange, $period);
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

        return $this->applyChartVisualizationSettings($chart, $dateRange, $period);
    }

    private function applyChartVisualizationSettings(
        Exchange $chart,
        ?array $dateRange,
        ?string $period
    ): Exchange {
        $visualization = $chart['visualization'] ?? [];
        if (!is_array($visualization)) {
            $visualization = [];
        }
        $visualization['labelIntervalHours'] = $this->resolveChartLabelIntervalHours($dateRange, $period);
        $visualization['allowSparseTimeline'] = true;
        if (!isset($visualization['allowCapacityChannel'])) {
            $visualization['allowCapacityChannel'] = $this->exchangeSupportsCapacityChannel($chart);
        }
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
            $params = $this->buildCapacityFetchParams($dateRange, $period, $fetchHourly);
            $urlPeriod = $this->resolveCapacityFetchUrlPeriod($dateRange, $period);

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

            return $this->normalizeFetchedCapacityExchange($exchange, $fetchHourly);
        } catch (\Throwable $exception) {
            return null;
        }
    }

    /**
     * @return array<string, string>
     */
    private function buildCapacityFetchParams(?array $dateRange, ?string $period, bool $fetchHourly): array
    {
        $params = [];

        if ($dateRange) {
            $params['fromDate'] = $dateRange['from'];
            $params['toDate'] = $dateRange['to'];
        }

        if ($fetchHourly) {
            $params['groupby'] = 'hour';
        } elseif ($dateRange !== null || ($period !== null && $period !== '_')) {
            $params['groupby'] = 'day';
        }

        return $params;
    }

    private function resolveCapacityFetchUrlPeriod(?array $dateRange, ?string $period): string
    {
        if ($period && $period !== '_') {
            return $period;
        }

        if ($dateRange) {
            return $dateRange['from'];
        }

        return '_';
    }

    private function normalizeFetchedCapacityExchange(Exchange $exchange, bool $fetchHourly): Exchange
    {
        $exchange->data = $this->aggregateRowsByDate($exchange->data, $fetchHourly);

        if (!$fetchHourly) {
            if ($this->exchangeDataLooksHourly($exchange->data)) {
                $exchange->data = $this->aggregateRowsByDate($exchange->data, false);
            }
            $exchange->period = 'day';
        } elseif (($exchange->period ?? 'day') === 'hour') {
            $exchange->period = 'hour';
        }

        return $exchange;
    }

    /**
     * Sum booked/planned counts per date (or hour) across multiple scopes.
     *
     * @param array<int, array<int, mixed>> $rows
     * @param bool $useHourlyKeys true = one row per clock hour, false = per calendar day
     * @return array<int, array<int, mixed>>
     */
    public function aggregateRowsByDate(array $rows, bool $useHourlyKeys): array
    {
        $capacityRowsByTimelineKey = [];

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $normalized = $this->normalizeDataRow($row, $useHourlyKeys);
            $timelineKey = $normalized[1];
            if ($timelineKey === '') {
                continue;
            }

            if (!isset($capacityRowsByTimelineKey[$timelineKey])) {
                $capacityRowsByTimelineKey[$timelineKey] = $normalized;
                continue;
            }

            for ($column = 2; $column <= 9; $column++) {
                $capacityRowsByTimelineKey[$timelineKey][$column] += $normalized[$column];
            }
        }

        ksort($capacityRowsByTimelineKey);

        return array_values($capacityRowsByTimelineKey);
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
        $timelineBounds = $this->resolveTimelineBounds($dateRange, $period);
        if (!$timelineBounds) {
            return $rows;
        }

        $capacityRowsByTimelineKey = [];
        $defaultSubjectId = '';

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $normalized = $this->normalizeDataRow($row, $useHourlyTimeline);
            $timelineKey = $normalized[1];
            if ($timelineKey === '') {
                continue;
            }

            if (!isset($capacityRowsByTimelineKey[$timelineKey])) {
                $capacityRowsByTimelineKey[$timelineKey] = $normalized;
                if ($defaultSubjectId === '' && $normalized[0] !== '') {
                    $defaultSubjectId = $normalized[0];
                }
                continue;
            }

            for ($column = 2; $column <= 9; $column++) {
                $capacityRowsByTimelineKey[$timelineKey][$column] += $normalized[$column];
            }
        }

        $rangeStartDate = $timelineBounds['from'];
        $rangeEndDate = $timelineBounds['to'];
        $filledTimelineRows = [];

        if ($useHourlyTimeline) {
            $start = new DateTimeImmutable($rangeStartDate . ' 00:00:00');
            $end = new DateTimeImmutable($rangeEndDate . ' 23:00:00');
            $cursor = $start;

            while ($cursor <= $end) {
                $timelineKey = $this->normalizeTimelineKey(
                    $cursor->format('Y-m-d') . ' ' . $cursor->format('H') . ':00',
                    true
                );
                $filledTimelineRows[] = $capacityRowsByTimelineKey[$timelineKey]
                    ?? $this->emptyCapacityRow($defaultSubjectId, $timelineKey);
                $cursor = $cursor->modify('+1 hour');
            }

            return $filledTimelineRows;
        }

        $start = new DateTimeImmutable($rangeStartDate);
        $end = new DateTimeImmutable($rangeEndDate);
        $cursor = $start;

        while ($cursor <= $end) {
            $timelineKey = $cursor->format('Y-m-d');
            $filledTimelineRows[] = $capacityRowsByTimelineKey[$timelineKey]
                ?? $this->emptyCapacityRow($defaultSubjectId, $timelineKey);
            $cursor = $cursor->modify('+1 day');
        }

        return $filledTimelineRows;
    }

    /**
     * @param array<int, array<int, mixed>> $rows
     */
    private function exchangeDataLooksHourly(array $rows): bool
    {
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $date = $this->rowDateValue($row);
            if ($date !== '' && preg_match('/\d{2}:\d{2}/', $date)) {
                return true;
            }
        }

        return false;
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
     * @return array<int, int|string>
     */
    private function emptyCapacityRow(string $subjectId, string $key): array
    {
        return [$subjectId, $key, 0, 0, 0, 0, 0, 0, 0, 0];
    }

    public function exchangeSupportsCapacityChannel(Exchange $exchange): bool
    {
        foreach ($exchange->dictionary ?? [] as $entry) {
            if (($entry['variable'] ?? null) === 'bookedcount_public') {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<int|string, mixed> $row
     * @return array{0: string, 1: string, 2: int, 3: int, 4: int, 5: int, 6: int, 7: int, 8: int, 9: int}
     */
    private function normalizeDataRow(array $row, bool $useHourlyKeys): array
    {
        $date = $this->rowDateValue($row);

        return [
            (string) ($row['subjectid'] ?? $row[0] ?? ''),
            $this->normalizeTimelineKey($date, $useHourlyKeys),
            $this->rowNumericValue($row, 'bookedcount', 2),
            $this->rowNumericValue($row, 'plannedcount', 3),
            $this->rowNumericValue($row, 'bookedminutes', 4),
            $this->rowNumericValue($row, 'plannedminutes', 5),
            $this->rowNumericValue($row, 'bookedcount_public', 6),
            $this->rowNumericValue($row, 'plannedcount_public', 7),
            $this->rowNumericValue($row, 'bookedminutes_public', 8),
            $this->rowNumericValue($row, 'plannedminutes_public', 9),
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

    /**
     * Prepare download arguments for capacity report Excel export.
     */
    public function prepareDownloadArgs(
        array $args,
        string $scopeId,
        mixed $exchangeCapacity,
        ?array $dateRange,
        array $selectedScopes = []
    ): array {
        $args['category'] = 'raw-capacityscope';
        $args['subject'] = 'capacityscope';
        $args['subjectid'] = $scopeId;

        if ($dateRange) {
            $args['period'] = $dateRange['from'] . '_' . $dateRange['to'];
        } elseif (!isset($args['period']) || $args['period'] === null || $args['period'] === '') {
            $args['period'] = '_';
        }

        if (!empty($selectedScopes)) {
            $args['selectedScopes'] = $selectedScopes;
        }

        if ($exchangeCapacity instanceof Exchange) {
            $args['report'] = $exchangeCapacity;
        }

        return $args;
    }
}
