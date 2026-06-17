<?php

/**
 * @package Zmsstatistic
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic\Service;

use BO\Zmsentities\Exchange;
use DateTimeImmutable;

/**
 * @SuppressWarnings(TooManyMethods)
 */
class ReportCapacityService
{
    /** Fetch hour-level warehouse data up to this range length (chart buckets are derived in PHP). */
    private const MAX_HOURLY_FETCH_HOURS = 336;

    /** Above this count, slot-time hints group by duration instead of listing scope names. */
    private const SLOT_TIME_HINT_MAX_NAMED_SCOPES = 4;

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

    /** Available date range per scope from warehouse subject list (periodstart / periodend). */
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

            $dateBoundsByScopeId = [];
            foreach ($subjectList->data as $row) {
                $scopeId = (string) ($row[0] ?? '');
                $periodStart = (string) ($row[1] ?? '');
                $periodEnd = (string) ($row[2] ?? '');

                if ($scopeId === '' || $periodStart === '' || $periodEnd === '') {
                    continue;
                }

                if (!isset($dateBoundsByScopeId[$scopeId])) {
                    $dateBoundsByScopeId[$scopeId] = [
                        'min' => $periodStart,
                        'max' => $periodEnd,
                    ];
                    continue;
                }

                $dateBoundsByScopeId[$scopeId]['min'] = min($dateBoundsByScopeId[$scopeId]['min'], $periodStart);
                $dateBoundsByScopeId[$scopeId]['max'] = max($dateBoundsByScopeId[$scopeId]['max'], $periodEnd);
            }

            return $dateBoundsByScopeId;
        } catch (\Throwable $exception) {
            return [];
        }
    }

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

            $scopeSlotTimeEntries = [];
            foreach ($scopeIds as $scopeId) {
                $id = (string) $scopeId;
                if (!isset($scopeById[$id])) {
                    continue;
                }

                $scope = $scopeById[$id];
                $scopeSlotTimeEntries[] = [
                    'id' => $id,
                    'name' => $this->resolveScopeDisplayName($scope, $id),
                    'slotTimeInMinutes' => $this->resolveScopeSlotTimeMinutes($scope),
                ];
            }

            return $scopeSlotTimeEntries;
        } catch (\Throwable $exception) {
            return [];
        }
    }

    public function formatScopeSlotTimeHint(array $scopeSlotTimes): ?string
    {
        if ($scopeSlotTimes === []) {
            return null;
        }

        $slotTimeMinutesList = array_values(array_filter(
            array_map(
                static fn (array $item): ?int => $item['slotTimeInMinutes'] ?? null,
                $scopeSlotTimes
            ),
            static fn (?int $minutes): bool => $minutes !== null
        ));

        if ($slotTimeMinutesList === []) {
            return null;
        }

        if (count($scopeSlotTimes) === 1) {
            return sprintf(
                'Zeitschlitzdauer laut Öffnungszeit: %d Min.',
                $slotTimeMinutesList[0]
            );
        }

        $uniqueSlotTimeMinutes = array_values(array_unique($slotTimeMinutesList));
        if (count($uniqueSlotTimeMinutes) === 1 && count($slotTimeMinutesList) === count($scopeSlotTimes)) {
            return sprintf(
                'Zeitschlitzdauer laut Öffnungszeit: %d Min. (alle ausgewählten Standorte)',
                $uniqueSlotTimeMinutes[0]
            );
        }

        if (count($scopeSlotTimes) > self::SLOT_TIME_HINT_MAX_NAMED_SCOPES) {
            return $this->formatGroupedScopeSlotTimeHint($scopeSlotTimes);
        }

        $hintParts = [];
        foreach ($scopeSlotTimes as $item) {
            if (($item['slotTimeInMinutes'] ?? null) === null) {
                continue;
            }

            $hintParts[] = sprintf(
                '%s: %d Min.',
                $item['name'],
                $item['slotTimeInMinutes']
            );
        }

        return $hintParts === []
            ? null
            : 'Zeitschlitzdauer laut Öffnungszeit: ' . implode('; ', $hintParts);
    }

    private function formatGroupedScopeSlotTimeHint(array $scopeSlotTimes): ?string
    {
        $scopeCountBySlotMinutes = [];
        foreach ($scopeSlotTimes as $item) {
            $minutes = $item['slotTimeInMinutes'] ?? null;
            if ($minutes === null) {
                continue;
            }

            $scopeCountBySlotMinutes[$minutes] = ($scopeCountBySlotMinutes[$minutes] ?? 0) + 1;
        }

        if ($scopeCountBySlotMinutes === []) {
            return null;
        }

        ksort($scopeCountBySlotMinutes, SORT_NUMERIC);

        $hintParts = [];
        foreach ($scopeCountBySlotMinutes as $minutes => $scopeCount) {
            $hintParts[] = sprintf(
                '%d Min. (%d %s)',
                $minutes,
                $scopeCount,
                $scopeCount === 1 ? 'Standort' : 'Standorte'
            );
        }

        return 'Zeitschlitzdauer laut Öffnungszeit: ' . implode(', ', $hintParts);
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

        $timelineBounds = $this->resolveTimelineBounds(null, $period);
        if ($timelineBounds) {
            $exchange->data = $this->filterRowsByBounds($exchange->data, $timelineBounds);
        }

        if (empty($exchange->data)) {
            return null;
        }

        if ($timelineBounds) {
            return $this->finalizeExchange(
                $exchange,
                $timelineBounds['from'],
                $timelineBounds['to']
            );
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
        $chartExchange = clone $exchange;
        $useHourlyTimeline = $this->shouldFetchHourlyFromApi($dateRange, $period);

        $chartExchange->data = $this->fillMissingTimeline(
            $chartExchange->data,
            $dateRange,
            $period,
            $useHourlyTimeline
        );

        return $this->applyChartVisualizationSettings($chartExchange, $dateRange, $period);
    }

    private function applyChartVisualizationSettings(
        Exchange $chartExchange,
        ?array $dateRange,
        ?string $period
    ): Exchange {
        $visualization = $chartExchange['visualization'] ?? [];
        if (!is_array($visualization)) {
            $visualization = [];
        }
        $visualization['labelIntervalHours'] = $this->resolveChartLabelIntervalHours($dateRange, $period);
        $visualization['allowSparseTimeline'] = true;
        if (!isset($visualization['allowCapacityChannel'])) {
            $visualization['allowCapacityChannel'] = $this->exchangeSupportsCapacityChannel($chartExchange);
        }
        $chartExchange['visualization'] = $visualization;

        return $chartExchange;
    }

    /**
     * X-axis tick label spacing in hours, or null for daily labels (data stays hourly/daily respectively).
     */
    public function resolveChartLabelIntervalHours(?array $dateRange, ?string $period): ?int
    {
        $rangeDurationHours = $this->resolveRangeDurationHours($dateRange, $period);
        if ($rangeDurationHours === null) {
            return null;
        }

        if ($rangeDurationHours <= 24) {
            return 1;
        }

        if ($rangeDurationHours <= 48) {
            return 2;
        }

        if ($rangeDurationHours <= 168) {
            return 6;
        }

        if ($rangeDurationHours <= self::MAX_HOURLY_FETCH_HOURS) {
            return 12;
        }

        return null;
    }

    public function shouldFetchHourlyFromApi(?array $dateRange, ?string $period): bool
    {
        $rangeDurationHours = $this->resolveRangeDurationHours($dateRange, $period);
        if ($rangeDurationHours !== null) {
            return $rangeDurationHours <= self::MAX_HOURLY_FETCH_HOURS;
        }

        if ($period && $period !== '_' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $period)) {
            return true;
        }

        return false;
    }

    /** Length of selected range in hours (inclusive end day). */
    public function resolveRangeDurationHours(?array $dateRange, ?string $period): ?float
    {
        $bounds = $this->resolveTimelineBounds($dateRange, $period);
        if (!$bounds) {
            return null;
        }

        $rangeStart = new DateTimeImmutable($bounds['from'] . ' 00:00:00');
        $rangeEnd = new DateTimeImmutable($bounds['to'] . ' 23:59:59');

        return ($rangeEnd->getTimestamp() - $rangeStart->getTimestamp()) / 3600;
    }

    private function fetchAggregatedReport(string $scopeId, ?array $dateRange, ?string $period): ?Exchange
    {
        try {
            $useHourlyGrouping = $this->shouldFetchHourlyFromApi($dateRange, $period);
            $warehouseFetchParams = $this->buildCapacityFetchParams($dateRange, $period, $useHourlyGrouping);
            $warehouseUrlPeriodSegment = $this->resolveCapacityFetchUrlPeriod($dateRange, $period);

            $result = \App::$http->readGetResult(
                '/warehouse/capacityscope/' . $scopeId . '/' . $warehouseUrlPeriodSegment . '/',
                $warehouseFetchParams === [] ? null : $warehouseFetchParams
            );
            if (!$result) {
                return null;
            }

            $exchange = $result->getEntity();
            if (!$exchange instanceof Exchange) {
                return null;
            }

            return $this->normalizeFetchedCapacityExchange($exchange, $useHourlyGrouping);
        } catch (\Throwable $exception) {
            return null;
        }
    }

    private function buildCapacityFetchParams(?array $dateRange, ?string $period, bool $useHourlyGrouping): array
    {
        $warehouseFetchParams = [];

        if ($dateRange) {
            $warehouseFetchParams['fromDate'] = $dateRange['from'];
            $warehouseFetchParams['toDate'] = $dateRange['to'];
        }

        if ($useHourlyGrouping) {
            $warehouseFetchParams['groupby'] = 'hour';
        } elseif ($dateRange !== null || ($period !== null && $period !== '_')) {
            $warehouseFetchParams['groupby'] = 'day';
        }

        return $warehouseFetchParams;
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

    private function normalizeFetchedCapacityExchange(Exchange $exchange, bool $useHourlyGrouping): Exchange
    {
        $exchange->data = $this->aggregateRowsByDate($exchange->data, $useHourlyGrouping);

        if (!$useHourlyGrouping) {
            if ($this->exchangeDataLooksHourly($exchange->data)) {
                $exchange->data = $this->aggregateRowsByDate($exchange->data, false);
            }
            $exchange->period = 'day';
        } elseif (($exchange->period ?? 'day') === 'hour') {
            $exchange->period = 'hour';
        }

        return $exchange;
    }

    /** Sum booked/planned counts per date (or hour) across multiple scopes. */
    public function aggregateRowsByDate(array $rows, bool $useHourlyKeys): array
    {
        $rowsByTimelineKey = [];

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $normalizedRow = $this->normalizeDataRow($row, $useHourlyKeys);
            $timelineKey = $normalizedRow[1];
            if ($timelineKey === '') {
                continue;
            }

            if (!isset($rowsByTimelineKey[$timelineKey])) {
                $rowsByTimelineKey[$timelineKey] = $normalizedRow;
                continue;
            }

            for ($metricColumnIndex = 2; $metricColumnIndex <= 9; $metricColumnIndex++) {
                $rowsByTimelineKey[$timelineKey][$metricColumnIndex] += $normalizedRow[$metricColumnIndex];
            }
        }

        ksort($rowsByTimelineKey);

        return array_values($rowsByTimelineKey);
    }

    /**
     * Insert zero rows for each hour or day in the selected range so the chart shows
     * closed periods and gaps between days (not only timestamps with slot data).
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

        $rowsByTimelineKey = [];
        $defaultSubjectId = '';

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $normalizedRow = $this->normalizeDataRow($row, $useHourlyTimeline);
            $timelineKey = $normalizedRow[1];
            if ($timelineKey === '') {
                continue;
            }

            if (!isset($rowsByTimelineKey[$timelineKey])) {
                $rowsByTimelineKey[$timelineKey] = $normalizedRow;
                if ($defaultSubjectId === '' && $normalizedRow[0] !== '') {
                    $defaultSubjectId = $normalizedRow[0];
                }
                continue;
            }

            for ($metricColumnIndex = 2; $metricColumnIndex <= 9; $metricColumnIndex++) {
                $rowsByTimelineKey[$timelineKey][$metricColumnIndex] += $normalizedRow[$metricColumnIndex];
            }
        }

        $rangeStartDate = $timelineBounds['from'];
        $rangeEndDate = $timelineBounds['to'];
        $completeTimelineRows = [];

        if ($useHourlyTimeline) {
            $timelineStart = new DateTimeImmutable($rangeStartDate . ' 00:00:00');
            $timelineEnd = new DateTimeImmutable($rangeEndDate . ' 23:00:00');
            $timelineCursor = $timelineStart;

            while ($timelineCursor <= $timelineEnd) {
                $timelineKey = $this->normalizeTimelineKey(
                    $timelineCursor->format('Y-m-d') . ' ' . $timelineCursor->format('H') . ':00',
                    true
                );
                $completeTimelineRows[] = $rowsByTimelineKey[$timelineKey]
                    ?? $this->emptyCapacityRow($defaultSubjectId, $timelineKey);
                $timelineCursor = $timelineCursor->modify('+1 hour');
            }

            return $completeTimelineRows;
        }

        $timelineStart = new DateTimeImmutable($rangeStartDate);
        $timelineEnd = new DateTimeImmutable($rangeEndDate);
        $timelineCursor = $timelineStart;

        while ($timelineCursor <= $timelineEnd) {
            $timelineKey = $timelineCursor->format('Y-m-d');
            $completeTimelineRows[] = $rowsByTimelineKey[$timelineKey]
                ?? $this->emptyCapacityRow($defaultSubjectId, $timelineKey);
            $timelineCursor = $timelineCursor->modify('+1 day');
        }

        return $completeTimelineRows;
    }

    private function exchangeDataLooksHourly(array $rows): bool
    {
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $dateValue = $this->rowDateValue($row);
            if ($dateValue !== '' && preg_match('/\d{2}:\d{2}/', $dateValue)) {
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

    private function emptyCapacityRow(string $subjectId, string $timelineKey): array
    {
        return [$subjectId, $timelineKey, 0, 0, 0, 0, 0, 0, 0, 0];
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

    private function normalizeDataRow(array $row, bool $useHourlyKeys): array
    {
        $rowDateValue = $this->rowDateValue($row);

        return [
            (string) ($row['subjectid'] ?? $row[0] ?? ''),
            $this->normalizeTimelineKey($rowDateValue, $useHourlyKeys),
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

    private function normalizeTimelineKey(string $rowDateValue, bool $useHourlyKeys): string
    {
        if ($rowDateValue === '') {
            return '';
        }

        if (!$useHourlyKeys) {
            return substr($rowDateValue, 0, 10);
        }

        $timestamp = strtotime($rowDateValue);
        if ($timestamp === false) {
            return $rowDateValue;
        }

        return date('Y-m-d H', $timestamp) . ':00';
    }

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

    private function filterRowsByBounds(array $rows, array $bounds): array
    {
        $rangeStart = $bounds['from'];
        $rangeEnd = $bounds['to'];

        return array_values(array_filter($rows, static function ($row) use ($rangeStart, $rangeEnd) {
            $date = (string) ($row[1] ?? '');
            if ($date === '') {
                return false;
            }

            $day = substr($date, 0, 10);

            return $day >= $rangeStart && $day <= $rangeEnd;
        }));
    }

    private function finalizeExchange(Exchange $exchange, ?string $fromDate = null, ?string $toDate = null): Exchange
    {
        if ($fromDate && $toDate) {
            $exchange->firstDay = $this->dayFromString($fromDate);
            $exchange->lastDay = $this->dayFromString($toDate);
        } elseif (!empty($exchange->data)) {
            $firstRowDate = (string) $exchange->data[0][1];
            $lastRowDate = (string) $exchange->data[count($exchange->data) - 1][1];
            $exchange->firstDay = $this->dayFromString(substr($firstRowDate, 0, 10));
            $exchange->lastDay = $this->dayFromString(substr($lastRowDate, 0, 10));
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
            $rowDateValue = (string) ($row[1] ?? '');
            if ($rowDateValue === '') {
                continue;
            }

            $year = substr($rowDateValue, 0, 4);
            $month = substr($rowDateValue, 0, 7);

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

    public function buildDownloadFilename(?array $dateRange, ?string $period, string $valueMode = 'slots'): string
    {
        $valueSuffix = $valueMode === 'minutes' ? '-minuten' : '-zeitschlitze';
        $rangePart = $this->buildDownloadDateRangePart($dateRange, $period);
        $baseName = 'terminkapazitaet' . $valueSuffix;

        if ($rangePart === '') {
            return $baseName;
        }

        return $baseName . '_' . $rangePart;
    }

    private function buildDownloadDateRangePart(?array $dateRange, ?string $period): string
    {
        if ($dateRange !== null && !empty($dateRange['from']) && !empty($dateRange['to'])) {
            return $dateRange['from'] . '-bis-' . $dateRange['to'];
        }

        if ($period === null || $period === '' || $period === '_') {
            return '';
        }

        if (preg_match('/^(\d{4}-\d{2}-\d{2})_(\d{4}-\d{2}-\d{2})$/', $period, $matches)) {
            return $matches[1] . '-bis-' . $matches[2];
        }

        return preg_replace('/[^0-9-]/', '', $period);
    }

    public function buildDownloadExchange(
        Exchange $exchange,
        string $channelMode = 'total',
        string $valueMode = 'slots'
    ): Exchange {
        $channelMode = in_array($channelMode, ['total', 'public', 'intern_only'], true)
            ? $channelMode
            : 'total';
        $useMinutes = $valueMode === 'minutes' && $this->exchangeSupportsMinutes($exchange);
        $isHourly = ($exchange->period ?? '') === 'hour';

        $download = new Exchange();
        $download->addDictionaryEntry(
            'date',
            'string',
            $isHourly ? 'Zeitpunkt' : 'Datum'
        );
        $download->addDictionaryEntry(
            'planned',
            'number',
            $this->buildCapacityMetricLabel('planned', $channelMode, $useMinutes)
        );
        $download->addDictionaryEntry(
            'booked',
            'number',
            $this->buildCapacityMetricLabel('booked', $channelMode, $useMinutes)
        );
        $download->addDictionaryEntry('utilization', 'string', 'Auslastung');

        $totalPlanned = 0;
        $totalBooked = 0;

        foreach ($exchange->data as $row) {
            if (!is_array($row)) {
                continue;
            }

            $normalizedRow = $this->normalizeDataRow($row, $isHourly);
            $planned = $this->resolveChannelMetric($normalizedRow, 'planned', $channelMode, $useMinutes);
            $booked = $this->resolveChannelMetric($normalizedRow, 'booked', $channelMode, $useMinutes);
            $totalPlanned += $planned;
            $totalBooked += $booked;
            $utilization = $planned > 0 ? round(($booked / $planned) * 1000) / 10 : 0;

            $download->addDataSet([
                $this->formatDownloadDate((string) $normalizedRow[1], $isHourly),
                (string) $planned,
                (string) $booked,
                $this->formatUtilizationPercent($utilization),
            ]);
        }

        $totalUtilization = $totalPlanned > 0
            ? round(($totalBooked / $totalPlanned) * 1000) / 10
            : 0;
        $download->addDataSet([
            'Summe',
            (string) $totalPlanned,
            (string) $totalBooked,
            $this->formatUtilizationPercent($totalUtilization),
        ]);

        return $download;
    }

    private function exchangeSupportsMinutes(Exchange $exchange): bool
    {
        foreach ($exchange->dictionary ?? [] as $entry) {
            if (($entry['variable'] ?? null) === 'bookedminutes') {
                return true;
            }
        }

        return false;
    }

    private function buildCapacityMetricLabel(string $kind, string $channelMode, bool $useMinutes): string
    {
        $unit = $useMinutes ? 'Minuten' : 'Zeitschlitze';
        $prefix = $kind === 'planned' ? 'Geplante' : 'Gebuchte';

        return $prefix . ' Kapazität ' . $this->resolveCapacityChannelLabel($channelMode) . ' (' . $unit . ')';
    }

    private function resolveCapacityChannelLabel(string $channelMode): string
    {
        if ($channelMode === 'public') {
            return 'Internet';
        }
        if ($channelMode === 'intern_only') {
            return 'nur intern';
        }

        return 'insgesamt';
    }

    private function resolveChannelMetric(
        array $normalizedRow,
        string $metric,
        string $channelMode,
        bool $useMinutes
    ): int {
        if ($useMinutes) {
            $totalPosition = $metric === 'planned' ? 5 : 4;
            $publicPosition = $metric === 'planned' ? 9 : 8;
        } else {
            $totalPosition = $metric === 'planned' ? 3 : 2;
            $publicPosition = $metric === 'planned' ? 7 : 6;
        }

        $total = (int) ($normalizedRow[$totalPosition] ?? 0);
        $public = (int) ($normalizedRow[$publicPosition] ?? 0);

        if ($channelMode === 'public') {
            return $public;
        }
        if ($channelMode === 'intern_only') {
            return max(0, $total - $public);
        }

        return $total;
    }

    private function formatDownloadDate(string $dateValue, bool $isHourly): string
    {
        if ($isHourly || !preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $dateValue, $matches)) {
            return $dateValue;
        }

        return $matches[3] . '.' . $matches[2] . '.' . $matches[1];
    }

    private function formatUtilizationPercent(float $utilization): string
    {
        $formatted = number_format($utilization, 1, ',', '.');
        if (str_ends_with($formatted, ',0')) {
            $formatted = substr($formatted, 0, -2);
        }

        return $formatted . ' %';
    }

    /**
     * Prepare download arguments for capacity report Excel export.
     */
    public function prepareDownloadArgs(
        array $args,
        string $scopeId,
        mixed $exchangeCapacity,
        ?array $dateRange,
        array $selectedScopes = [],
        string $valueMode = 'slots',
        string $channelMode = 'total'
    ): array {
        $args['category'] = 'capacityscope';
        $args['subject'] = 'capacityscope';
        $args['subjectid'] = $scopeId;

        if ($dateRange) {
            $args['period'] = $dateRange['from'] . '_' . $dateRange['to'];
        } elseif (!isset($args['period']) || $args['period'] === null || $args['period'] === '') {
            $args['period'] = '_';
        }

        $args['downloadTitle'] = $this->buildDownloadFilename(
            $dateRange,
            $args['period'] ?? null,
            $valueMode
        );

        if (!empty($selectedScopes)) {
            $args['selectedScopes'] = $selectedScopes;
        } elseif ($scopeId !== '') {
            $args['selectedScopes'] = array_values(array_filter(explode(',', $scopeId)));
        }

        if ($exchangeCapacity instanceof Exchange) {
            $args['reports'] = [$exchangeCapacity];
            $args['report'] = $this->buildDownloadExchange(
                $exchangeCapacity,
                $channelMode,
                $valueMode
            );
        }

        return $args;
    }
}
