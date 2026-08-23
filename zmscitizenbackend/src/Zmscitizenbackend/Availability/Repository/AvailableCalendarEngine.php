<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Availability\Repository;

use BO\Zmscitizenbackend\Connection\Pdo;
use BO\Zmscitizenbackend\Availability\Exception\CalendarWithoutScopes;
use BO\Zmscitizenbackend\Availability\Exception\InvalidAvailabilityInput;

/**
 * Combined calendar days + free appointment slots in a single DB pass.
 *
 * Port of zmsbackend CalendarAvailability + Day temp table + ProcessStatusFree
 * availability reads onto zmscitizenbackend's own PDO.
 *
 * @SuppressWarnings(Coupling)
 * @SuppressWarnings(TooManyMethods)
 * @SuppressWarnings(TooManyPublicMethods)
 */
class AvailableCalendarEngine
{
    private bool $tempScopeListExists = false;

    public function __construct(private Pdo $pdo)
    {
    }

    public function readFromQuery(
        \DateTimeInterface $now,
        string $startDate,
        string $endDate,
        string $officeIds,
        string $serviceIds,
        string $serviceCounts = '',
        ?string $slotsStartDate = null,
        ?string $slotsEndDate = null
    ): array {
        if ($startDate === '' || $endDate === '' || $officeIds === '' || $serviceIds === '') {
            throw new InvalidAvailabilityInput(
                'startDate, endDate, officeIds and serviceIds are required'
            );
        }

        try {
            $startDate = (new \DateTimeImmutable($startDate))->format('Y-m-d');
            $endDate = (new \DateTimeImmutable($endDate))->format('Y-m-d');
        } catch (\Exception) {
            throw new InvalidAvailabilityInput(
                'startDate and endDate must be valid dates (YYYY-MM-DD)'
            );
        }

        if ($startDate > $endDate) {
            throw new InvalidAvailabilityInput(
                'startDate must not be after endDate'
            );
        }

        try {
            [$slotsStartDate, $slotsEndDate] = $this->resolveSlotsDateRange(
                $startDate,
                $endDate,
                $slotsStartDate,
                $slotsEndDate
            );

            $calendar = $this->buildCalendarFromQuery(
                $startDate,
                $endDate,
                $officeIds,
                $serviceIds,
                $serviceCounts
            );

            return $this->readAvailability(
                $calendar,
                $now,
                $slotsStartDate,
                $slotsEndDate
            );
        } finally {
            $this->dropTemporaryScopeList();
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function readAvailability(
        BookingCalendar $calendar,
        \DateTimeInterface $now,
        string $slotsStartDate,
        string $slotsEndDate
    ): array {
        $slotsRequired = 0;
        $slotType = 'public';
        $dayRangeStart = $this->formatCalendarDate($calendar->firstDay);
        $dayRangeEnd = $this->formatCalendarDate($calendar->lastDay);
        [$slotsStartDate, $slotsEndDate] = $this->resolveSlotsDateRange(
            $dayRangeStart,
            $dayRangeEnd,
            $slotsStartDate,
            $slotsEndDate
        );

        [$responseStartDate, $responseEndDate] = $this->resolveResponseDaysRange(
            $slotsStartDate,
            $slotsEndDate,
            $dayRangeStart,
            $dayRangeEnd
        );

        $calendar = $this->resolveScopesAndSlots($calendar);
        $calendar = $this->withoutUnrelatedScopes($calendar, $slotsRequired);

        $bookableDays = $this->readBookableDaysForRange(
            $calendar,
            $slotsRequired,
            $slotType,
            $now,
            $responseStartDate,
            $responseEndDate,
            false
        );

        $slotDays = $this->filterDaysInDateRange($bookableDays, $slotsStartDate, $slotsEndDate);
        $responseDays = $this->filterDaysInDateRange($bookableDays, $responseStartDate, $responseEndDate);

        if ($slotsStartDate !== $slotsEndDate) {
            [$slotsStartDate, $slotsEndDate, $slotDays] = $this->narrowSlotsWindowToFirstBookableDay(
                $slotDays,
                $slotsStartDate,
                $slotsEndDate
            );
        } elseif (count($slotDays) === 0 && count($responseDays) > 0) {
            [$slotsStartDate, $slotsEndDate, $slotDays] = $this->narrowSlotsWindowToFirstBookableDay(
                $responseDays,
                $responseStartDate,
                $responseEndDate
            );
        }

        if (count($responseDays) === 0) {
            $firstBookableDate = $this->findFirstBookableDateAfter(
                $calendar,
                $slotsRequired,
                $slotType,
                $now,
                (new \DateTimeImmutable($responseStartDate))->modify('-1 day')->format('Y-m-d'),
                $dayRangeEnd
            );
            if ($firstBookableDate !== null) {
                $slotsStartDate = $firstBookableDate;
                $slotsEndDate = $firstBookableDate;
                [$responseStartDate, $responseEndDate] = $this->resolveResponseDaysRange(
                    $slotsStartDate,
                    $slotsEndDate,
                    $dayRangeStart,
                    $dayRangeEnd
                );
                $bookableDays = $this->readBookableDaysForRange(
                    $calendar,
                    $slotsRequired,
                    $slotType,
                    $now,
                    $responseStartDate,
                    $responseEndDate,
                    true
                );
                $responseDays = $this->filterDaysInDateRange(
                    $bookableDays,
                    $responseStartDate,
                    $responseEndDate
                );
                $slotDays = $this->filterDaysInDateRange(
                    $bookableDays,
                    $slotsStartDate,
                    $slotsEndDate
                );
            }
        }

        $calendar->days = $slotDays;

        $processList = [];
        if (count($slotDays) > 0) {
            $processList = $this->readFreeProcessesMinimalFromPreparedCalendar($calendar, $slotType, $slotsRequired);
        }

        [$prevBookableDate, $nextBookableDate] = $this->findAdjacentBookableDatesByScan(
            $calendar,
            $slotsRequired,
            $slotType,
            $now,
            $responseStartDate,
            $responseEndDate,
            $dayRangeStart,
            $dayRangeEnd
        );

        $calendar->days = $responseDays;
        return $this->buildResult(
            $calendar,
            $processList,
            $slotsStartDate,
            $slotsEndDate,
            $prevBookableDate,
            $nextBookableDate,
            $now
        );
    }

    private function withoutUnrelatedScopes(BookingCalendar $calendar, int $slotsRequiredForce): BookingCalendar
    {
        if ($slotsRequiredForce) {
            return $calendar;
        }

        foreach ($calendar->scopes as $key => $scope) {
            if ($calendar->requiredSlotsFor($scope) < 1) {
                unset($calendar->scopes[$key]);
            }
        }

        return $calendar;
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function resolveSlotsDateRange(
        string $startDate,
        string $endDate,
        ?string $slotsStartDate,
        ?string $slotsEndDate
    ): array {
        $slotsStart = $slotsStartDate ?: $startDate;
        $slotsEnd = $slotsEndDate ?: $endDate;

        try {
            $slotsStart = (new \DateTimeImmutable($slotsStart))->format('Y-m-d');
            $slotsEnd = (new \DateTimeImmutable($slotsEnd))->format('Y-m-d');
        } catch (\Exception) {
            throw new InvalidAvailabilityInput(
                'slotsStartDate and slotsEndDate must be valid dates (YYYY-MM-DD)'
            );
        }

        if ($slotsStart > $slotsEnd) {
            throw new InvalidAvailabilityInput(
                'slotsStartDate must not be after slotsEndDate'
            );
        }

        if ($slotsStart < $startDate) {
            $slotsStart = $startDate;
        }
        if ($slotsEnd > $endDate) {
            $slotsEnd = $endDate;
        }
        if ($slotsStart > $slotsEnd) {
            throw new InvalidAvailabilityInput(
                'slots date range does not overlap startDate/endDate'
            );
        }

        return [$slotsStart, $slotsEnd];
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function resolveResponseDaysRange(
        string $slotsStartDate,
        string $slotsEndDate,
        string $dayRangeStart,
        string $dayRangeEnd
    ): array {
        $monthStart = (new \DateTimeImmutable($slotsStartDate))
            ->modify('first day of this month')
            ->format('Y-m-d');
        $monthEnd = (new \DateTimeImmutable($slotsEndDate))
            ->modify('last day of this month')
            ->format('Y-m-d');

        if ($monthStart < $dayRangeStart) {
            $monthStart = $dayRangeStart;
        }
        if ($monthEnd > $dayRangeEnd) {
            $monthEnd = $dayRangeEnd;
        }
        if ($monthStart > $monthEnd) {
            return [$slotsStartDate, $slotsEndDate];
        }

        return [$monthStart, $monthEnd];
    }

    /**
     * @param array<int|string, array<string, mixed>> $days
     * @return array<int|string, array<string, mixed>>
     */
    private function filterDaysInDateRange(array $days, string $startDate, string $endDate): array
    {
        $filtered = [];
        foreach ($days as $key => $day) {
            $date = $this->formatDayIso($day);
            if ($date >= $startDate && $date <= $endDate) {
                $filtered[$key] = $day;
            }
        }

        return $filtered;
    }

    /**
     * @param array<int|string, array<string, mixed>> $slotDays
     * @return array{0: string, 1: string, 2: array<int|string, array<string, mixed>>}
     */
    private function narrowSlotsWindowToFirstBookableDay(
        array $slotDays,
        string $slotsStartDate,
        string $slotsEndDate
    ): array {
        $firstBookableDate = null;
        foreach ($slotDays as $day) {
            $date = $this->formatDayIso($day);
            if ($firstBookableDate === null || $date < $firstBookableDate) {
                $firstBookableDate = $date;
            }
        }

        if ($firstBookableDate === null) {
            return [$slotsStartDate, $slotsEndDate, []];
        }

        return [
            $firstBookableDate,
            $firstBookableDate,
            $this->filterDaysInDateRange($slotDays, $firstBookableDate, $firstBookableDate),
        ];
    }

    private function readBookableDaysForRange(
        BookingCalendar $calendar,
        int $slotsRequired,
        string $slotType,
        \DateTimeInterface $now,
        string $rangeStartDate,
        string $rangeEndDate,
        bool $rewrite
    ): array {
        $savedFirst = $calendar->firstDay;
        $savedLast = $calendar->lastDay;
        $calendar->firstDay = $this->datePartsFromIso($rangeStartDate);
        $calendar->lastDay = $this->datePartsFromIso($rangeEndDate);

        try {
            if ($rewrite) {
                $this->rewriteTemporaryScopeList($calendar, $slotsRequired);
            } else {
                $this->writeTemporaryScopeList($calendar, $slotsRequired);
            }
            $dayList = BookingCalendar::daysInRange(
                BookingCalendar::withStatusByType(
                    $this->readListFromPreparedTemporaryScopeList($slotsRequired),
                    $slotType,
                    $now
                ),
                $calendar->firstDateTime(),
                $calendar->lastDateTime()
            );
        } finally {
            $calendar->firstDay = $savedFirst;
            $calendar->lastDay = $savedLast;
        }

        $bookableDays = [];
        foreach ($dayList as $key => $day) {
            if (($day['status'] ?? null) === 'bookable') {
                $bookableDays[$key] = $day;
            }
        }

        return $bookableDays;
    }

    /**
     * @return array{0: ?string, 1: ?string}
     */
    private function findAdjacentBookableDatesByScan(
        BookingCalendar $calendar,
        int $slotsRequired,
        string $slotType,
        \DateTimeInterface $now,
        string $responseStartDate,
        string $responseEndDate,
        string $dayRangeStart,
        string $dayRangeEnd
    ): array {
        return [
            $this->findFirstBookableDateBefore(
                $calendar,
                $slotsRequired,
                $slotType,
                $now,
                $responseStartDate,
                $dayRangeStart
            ),
            $this->findFirstBookableDateAfter(
                $calendar,
                $slotsRequired,
                $slotType,
                $now,
                $responseEndDate,
                $dayRangeEnd
            ),
        ];
    }

    private function findFirstBookableDateAfter(
        BookingCalendar $calendar,
        int $slotsRequired,
        string $slotType,
        \DateTimeInterface $now,
        string $afterDate,
        string $horizonEnd
    ): ?string {
        $cursor = (new \DateTimeImmutable($afterDate))->modify('+1 day');
        $horizonEndDate = new \DateTimeImmutable($horizonEnd);
        if ($cursor > $horizonEndDate) {
            return null;
        }

        while ($cursor <= $horizonEndDate) {
            $monthFirst = $cursor->modify('first day of this month');
            $monthLast = $cursor->modify('last day of this month');
            if ($monthLast > $horizonEndDate) {
                $monthLast = $horizonEndDate;
            }

            $bookableDays = $this->readBookableDaysForRange(
                $calendar,
                $slotsRequired,
                $slotType,
                $now,
                $monthFirst->format('Y-m-d'),
                $monthLast->format('Y-m-d'),
                true
            );

            $firstInMonth = null;
            foreach ($bookableDays as $day) {
                $date = $this->formatDayIso($day);
                if ($date <= $afterDate || $date > $horizonEnd) {
                    continue;
                }
                if ($firstInMonth === null || $date < $firstInMonth) {
                    $firstInMonth = $date;
                }
            }
            if ($firstInMonth !== null) {
                return $firstInMonth;
            }

            $cursor = $monthFirst->modify('first day of next month');
        }

        return null;
    }

    private function findFirstBookableDateBefore(
        BookingCalendar $calendar,
        int $slotsRequired,
        string $slotType,
        \DateTimeInterface $now,
        string $beforeDate,
        string $horizonStart
    ): ?string {
        $cursor = (new \DateTimeImmutable($beforeDate))->modify('-1 day');
        $horizonStartDate = new \DateTimeImmutable($horizonStart);
        if ($cursor < $horizonStartDate) {
            return null;
        }

        while ($cursor >= $horizonStartDate) {
            $monthFirst = $cursor->modify('first day of this month');
            $monthLast = $cursor->modify('last day of this month');
            if ($monthFirst < $horizonStartDate) {
                $monthFirst = $horizonStartDate;
            }

            $bookableDays = $this->readBookableDaysForRange(
                $calendar,
                $slotsRequired,
                $slotType,
                $now,
                $monthFirst->format('Y-m-d'),
                $monthLast->format('Y-m-d'),
                true
            );

            $lastInMonth = null;
            foreach ($bookableDays as $day) {
                $date = $this->formatDayIso($day);
                if ($date >= $beforeDate || $date < $horizonStart) {
                    continue;
                }
                if ($lastInMonth === null || $date > $lastInMonth) {
                    $lastInMonth = $date;
                }
            }
            if ($lastInMonth !== null) {
                return $lastInMonth;
            }

            $cursor = $monthFirst->modify('last day of previous month');
        }

        return null;
    }

    /**
     * @param array<string, mixed> $day
     */
    private function formatDayIso(array $day): string
    {
        return BookingCalendar::dayIso($day);
    }

    private function buildCalendarFromQuery(
        string $startDate,
        string $endDate,
        string $officeIds,
        string $serviceIds,
        string $serviceCounts
    ): BookingCalendar {
        $calendar = new BookingCalendar();
        $calendar->firstDay = $this->datePartsFromIso($startDate);
        $calendar->lastDay = $this->datePartsFromIso($endDate);
        $this->addProvidersFromQuery($calendar, $officeIds);
        $this->addRequestsFromQuery($calendar, $serviceIds, $serviceCounts);

        return $calendar;
    }

    private function addProvidersFromQuery(BookingCalendar $calendar, string $officeIds): void
    {
        $officeIdList = $this->parseCsv($officeIds);
        $providerSources = $this->readSourceMap('provider', $officeIdList);

        foreach ($officeIdList as $officeId) {
            $source = $providerSources[$officeId] ?? null;
            if (!$source) {
                throw new InvalidAvailabilityInput(
                    'Unknown officeId: ' . $officeId
                );
            }

            $calendar->providers[] = [
                'id' => (int) $officeId,
                'source' => $source,
            ];
        }
    }

    private function addRequestsFromQuery(
        BookingCalendar $calendar,
        string $serviceIds,
        string $serviceCounts
    ): void {
        $serviceIdList = $this->parseCsv($serviceIds);
        $countList = $serviceCounts === '' ? [] : array_map('trim', explode(',', $serviceCounts));
        $requestSources = $this->readSourceMap(
            'request',
            array_values(array_filter(array_map('strval', $serviceIdList)))
        );

        foreach ($serviceIdList as $index => $serviceId) {
            $source = $requestSources[$serviceId] ?? null;
            if (!$source) {
                throw new InvalidAvailabilityInput(
                    'Unknown serviceId: ' . $serviceId
                );
            }

            $count = max(1, (int) ($countList[$index] ?? 1));
            if ($count > AvailableCalendarQueries::MAX_SLOTS) {
                throw new InvalidAvailabilityInput(
                    'serviceCount exceeds maximum of ' . AvailableCalendarQueries::MAX_SLOTS
                );
            }
            for ($slot = 0; $slot < $count; $slot++) {
                $calendar->requests[] = [
                    'id' => $serviceId,
                    'source' => $source,
                ];
            }
        }
    }

    /**
     * @param list<string> $ids
     * @return array<string, string>
     */
    private function readSourceMap(string $table, array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        [$placeholders, $params] = AvailableCalendarQueries::idPlaceholders($table, $ids);
        $sql = "SELECT id, source FROM {$table} WHERE id IN ({$placeholders})";
        $rows = $this->pdo->fetchAll($sql, $params);
        $map = [];
        foreach (is_array($rows) ? $rows : [] as $row) {
            $map[(string) $row['id']] = (string) $row['source'];
        }

        return $map;
    }

    private function resolveScopesAndSlots(BookingCalendar $calendar): BookingCalendar
    {
        $providerIdSet = $this->providerIdSet($calendar);
        $calendar->scopes = $this->readScopesForProviders(array_keys($providerIdSet));
        $calendar->uniqueScopes();
        if (count($calendar->scopes) < 1) {
            throw new CalendarWithoutScopes('No scopes resolved for the given offices');
        }

        $this->addRequiredSlotsFromRequests($calendar, $providerIdSet);

        return $calendar;
    }

    /**
     * @return array<string, true>
     */
    private function providerIdSet(BookingCalendar $calendar): array
    {
        $providerIdSet = [];
        foreach ($calendar->providers as $provider) {
            $providerIdSet[(string) $provider['id']] = true;
        }

        return $providerIdSet;
    }

    /**
     * @param list<string> $providerIds
     * @return array<int|string, array<string, mixed>>
     */
    private function readScopesForProviders(array $providerIds): array
    {
        $scopeList = [];
        if ($providerIds === []) {
            return $scopeList;
        }

        [$placeholders, $params] = AvailableCalendarQueries::idPlaceholders('provider', $providerIds);
        $sql = <<<SQL
SELECT
    standort.StandortID AS id,
    standort.source,
    standort.InfoDienstleisterID AS provider_id,
    provider.data AS provider_data
FROM standort
INNER JOIN provider
    ON provider.id = standort.InfoDienstleisterID
    AND provider.source = standort.source
WHERE standort.InfoDienstleisterID IN ({$placeholders})
ORDER BY standort.StandortID
SQL;
        $rows = $this->pdo->fetchAll($sql, $params);
        foreach (is_array($rows) ? $rows : [] as $row) {
            $scopeList[] = [
                'id' => (int) $row['id'],
                'source' => (string) $row['source'],
                'provider' => [
                    'id' => $row['provider_id'],
                    'source' => (string) $row['source'],
                    'data' => $this->decodeProviderData($row['provider_data'] ?? null),
                ],
            ];
        }

        return $scopeList;
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeProviderData(mixed $providerData): array
    {
        if (is_string($providerData) && $providerData !== '') {
            $decoded = json_decode($providerData, true);
            return is_array($decoded) ? $decoded : [];
        }

        return is_array($providerData) ? $providerData : [];
    }

    /**
     * @param array<string, true> $providerIdSet
     */
    private function addRequiredSlotsFromRequests(BookingCalendar $calendar, array $providerIdSet): void
    {
        $relationsByKey = $this->readRequestRelations($calendar);
        foreach ($calendar->requests as $request) {
            $key = (string) $request['source'] . ':' . (string) $request['id'];
            foreach ($relationsByKey[$key] ?? [] as $relation) {
                $providerId = (string) $relation['provider__id'];
                if ($providerIdSet !== [] && !isset($providerIdSet[$providerId])) {
                    continue;
                }
                $calendar->addRequiredSlots(
                    (string) $relation['source'],
                    $relation['provider__id'],
                    (int) $relation['slots']
                );
            }
        }
    }

    /**
     * @return array<string, list<array<string, mixed>>>
     */
    private function readRequestRelations(BookingCalendar $calendar): array
    {
        $requestIds = [];
        foreach ($calendar->requests as $request) {
            $requestIds[] = (string) $request['id'];
        }
        $requestIds = array_values(array_unique($requestIds));
        if ($requestIds === []) {
            return [];
        }

        [$placeholders, $params] = AvailableCalendarQueries::idPlaceholders('request', $requestIds);
        $sql = <<<SQL
SELECT request__id, provider__id, source, slots
FROM request_provider
WHERE bookable = 1
    AND request__id IN ({$placeholders})
SQL;
        $rows = $this->pdo->fetchAll($sql, $params);
        $relationsByKey = [];
        foreach (is_array($rows) ? $rows : [] as $row) {
            $key = (string) $row['source'] . ':' . (string) $row['request__id'];
            $relationsByKey[$key][] = $row;
        }

        return $relationsByKey;
    }

    private function writeTemporaryScopeList(BookingCalendar $calendar, int $slotsRequiredForce): void
    {
        $this->pdo->exec(AvailableCalendarQueries::QUERY_CREATE_TEMPORARY_SCOPELIST);
        $slotsRequired = $slotsRequiredForce;
        foreach ($calendar->monthStarts() as $dateTime) {
            foreach ($calendar->scopes as $scope) {
                if (!$slotsRequiredForce) {
                    $slotsRequired = $calendar->requiredSlotsFor($scope);
                }
                $this->pdo->perform(AvailableCalendarQueries::QUERY_INSERT_TEMPORARY_SCOPELIST, [
                    'scopeID' => $scope['id'],
                    'year' => $dateTime->format('Y'),
                    'month' => $dateTime->format('m'),
                    'slotsRequired' => $slotsRequired > 1 ? round($slotsRequired, 0) : 1,
                ]);
            }
        }
        $this->tempScopeListExists = true;
    }

    private function rewriteTemporaryScopeList(BookingCalendar $calendar, int $slotsRequiredForce): void
    {
        if ($this->tempScopeListExists) {
            $this->pdo->exec(AvailableCalendarQueries::QUERY_DROP_TEMPORARY_SCOPELIST);
            $this->tempScopeListExists = false;
        }
        $this->writeTemporaryScopeList($calendar, $slotsRequiredForce);
    }

    public function dropTemporaryScopeList(): void
    {
        try {
            $this->pdo->exec(AvailableCalendarQueries::QUERY_DROP_TEMPORARY_SCOPELIST);
        } catch (\Exception) {
            // ignore, table may not exist if resolve failed first
        }
        $this->tempScopeListExists = false;
    }

    /**
     * Resolve scopes and fill calendarscope for a single booking day on this PDO session.
     */
    public function prepareBookingCalendar(
        string $officeIds,
        string $serviceIds,
        string $serviceCounts,
        string $day
    ): BookingCalendar {
        $calendar = $this->buildCalendarFromQuery($day, $day, $officeIds, $serviceIds, $serviceCounts);
        $calendar = $this->resolveScopesAndSlots($calendar);
        $calendar = $this->withoutUnrelatedScopes($calendar, 0);
        if (count($calendar->scopes) < 1) {
            throw new CalendarWithoutScopes('No matching scopes found for given location(s)');
        }
        $this->writeTemporaryScopeList($calendar, 0);

        $parts = $this->datePartsFromIso($day);
        $dayData = [
            'year' => $parts['year'],
            'month' => $parts['month'],
            'day' => $parts['day'],
            'status' => 'bookable',
        ];
        $calendar->days = [
            BookingCalendar::dayHash($dayData) => $dayData,
        ];

        return $calendar;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function readListFromPreparedTemporaryScopeList(int $slotsRequiredForce): array
    {
        $dayList = [];
        $dayData = $this->pdo->fetchAll(
            AvailableCalendarQueries::QUERY_DAYLIST_JOIN_AVAILABILITY,
            [
                'forceRequiredSlots' =>
                    ($slotsRequiredForce < 1) ? 1 : round($slotsRequiredForce),
            ]
        );
        foreach (is_array($dayData) ? $dayData : [] as $day) {
            $day = BookingCalendar::nestRow(is_array($day) ? $day : []);
            $dayList[BookingCalendar::dayHash($day)] = $day;
        }

        return $dayList;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function readDeduplicatedFreeProcesses(
        BookingCalendar $calendar,
        string $slotType = 'public',
        int $slotsRequired = 0,
        bool $useAvailabilityQuery = true
    ): array {
        return $this->readFreeProcessesMinimalFromPreparedCalendar(
            $calendar,
            $slotType,
            $slotsRequired,
            $useAvailabilityQuery
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function readFreeProcessesMinimalFromPreparedCalendar(
        BookingCalendar $calendar,
        string $slotType,
        int $slotsRequired,
        bool $useAvailabilityQuery = true
    ): array {
        $days = $this->buildDaysListFromCalendarDays($calendar);
        if ($days === []) {
            return [];
        }

        $query = $useAvailabilityQuery
            ? AvailableCalendarQueries::QUERY_SELECT_PROCESSLIST_DAYS_AVAILABILITY
            : AvailableCalendarQueries::QUERY_SELECT_PROCESSLIST_DAYS;
        $sql = sprintf(
            $query,
            AvailableCalendarQueries::buildDaysCondition($days)
        );
        $rows = $this->pdo->fetchAll($sql, [
            'slotType' => $slotType,
            'forceRequiredSlots' => $slotsRequired < 1 ? 1 : intval($slotsRequired),
        ]);

        $processInfos = [];
        foreach (is_array($rows) ? $rows : [] as $item) {
            $processInfo = $this->extractProcessInfo($item, $calendar);
            if ($processInfo) {
                $processInfos[] = $processInfo;
            }
        }

        return $this->deduplicateWithRoundRobin($processInfos);
    }

    /**
     * @return list<\DateTimeInterface>
     */
    private function buildDaysListFromCalendarDays(BookingCalendar $calendar): array
    {
        if (!isset($calendar->days) || count($calendar->days) < 1) {
            return $this->buildDaysList($calendar);
        }

        $daysByDate = [];
        foreach ($calendar->days as $day) {
            $dateTime = BookingCalendar::dayToDateTime($day);
            $daysByDate[$dateTime->format('Y-m-d')] = $dateTime;
        }

        if ($daysByDate === []) {
            return $this->buildDaysList($calendar);
        }

        ksort($daysByDate);

        return array_values($daysByDate);
    }

    /**
     * @return list<\DateTimeInterface>
     */
    private function buildDaysList(BookingCalendar $calendar): array
    {
        $selectedDate = $calendar->firstDateTime();
        $days = [$selectedDate];
        $lastDay = $calendar->lastDateTime(false);
        if ($lastDay) {
            $days = [];
            while ($selectedDate <= $lastDay) {
                $days[] = $selectedDate;
                $selectedDate = $selectedDate->modify('+1 day');
            }
        }

        return $days;
    }

    /**
     * @param array<int, array<string, mixed>> $processInfos
     * @return array<int, array<string, mixed>>
     */
    private function deduplicateWithRoundRobin(array $processInfos): array
    {
        $candidatesByGroupTimestampKey = [];
        $groupTimestampKeyOrder = [];
        foreach ($processInfos as $processInfo) {
            $roundRobinGroupKey = self::resolveRoundRobinGroupKey(
                (string) $processInfo['providerId'],
                $processInfo['sharedBookingOfficeIds'] ?? null
            );
            $groupTimestampKey = $roundRobinGroupKey . '_' . $processInfo['date'];
            if (!isset($candidatesByGroupTimestampKey[$groupTimestampKey])) {
                $groupTimestampKeyOrder[] = $groupTimestampKey;
                $candidatesByGroupTimestampKey[$groupTimestampKey] = [];
            }
            $candidatesByGroupTimestampKey[$groupTimestampKey][] = $processInfo;
        }

        $roundRobinIndexByGroup = [];
        $deduplicatedProcesses = [];
        foreach ($groupTimestampKeyOrder as $groupTimestampKey) {
            $candidates = self::uniqueCandidatesSortedByScopeId(
                $candidatesByGroupTimestampKey[$groupTimestampKey]
            );
            $roundRobinGroupKey = self::resolveRoundRobinGroupKey(
                (string) $candidates[0]['providerId'],
                $candidates[0]['sharedBookingOfficeIds'] ?? null
            );
            $roundRobinTimeslotIndex = $roundRobinIndexByGroup[$roundRobinGroupKey] ?? 0;
            $chosenCandidate = $candidates[
                self::pickRoundRobinIndex($roundRobinTimeslotIndex, count($candidates))
            ];
            $roundRobinIndexByGroup[$roundRobinGroupKey] = $roundRobinTimeslotIndex + 1;
            $deduplicatedProcesses[] = $this->createMinimalProcess($chosenCandidate);
        }

        return $deduplicatedProcesses;
    }

    /**
     * @param array<int, array<string, mixed>> $candidates
     * @return array<int, array<string, mixed>>
     */
    private static function uniqueCandidatesSortedByScopeId(array $candidates): array
    {
        $candidatesByScopeId = [];
        foreach ($candidates as $candidate) {
            $candidatesByScopeId[(string) $candidate['scopeId']] = $candidate;
        }
        $uniqueCandidates = array_values($candidatesByScopeId);
        usort(
            $uniqueCandidates,
            static fn (array $left, array $right): int =>
                ((int) $left['scopeId']) <=> ((int) $right['scopeId'])
        );

        return $uniqueCandidates;
    }

    /**
     * @internal Exposed for unit tests.
     * @param array<int, int|string>|null $sharedBookingOfficeIds
     */
    public static function resolveRoundRobinGroupKey(
        string $providerId,
        ?array $sharedBookingOfficeIds
    ): string {
        if (!is_array($sharedBookingOfficeIds) || $sharedBookingOfficeIds === []) {
            return $providerId;
        }

        $sortedSharedOfficeIds = array_map('intval', $sharedBookingOfficeIds);
        sort($sortedSharedOfficeIds, SORT_NUMERIC);

        return implode(',', $sortedSharedOfficeIds);
    }

    /**
     * @internal Exposed for unit tests.
     */
    public static function pickRoundRobinIndex(int $timeslotIndex, int $candidateCount): int
    {
        if ($candidateCount < 1) {
            throw new \InvalidArgumentException('candidateCount must be >= 1');
        }

        return $timeslotIndex % $candidateCount;
    }

    /**
     * @param array<string, mixed> $item
     * @return array<string, mixed>|null
     */
    private function extractProcessInfo(array $item, BookingCalendar $calendar): ?array
    {
        $scopeId = $item['scope__id'] ?? null;
        $dateString = $item['appointments__0__date'] ?? null;

        if (!$scopeId || !$dateString) {
            return null;
        }

        $date = strtotime((string) $dateString);
        if (!$date) {
            return null;
        }

        $scope = $calendar->scopeById($scopeId);
        if (!$scope) {
            return null;
        }

        $provider = $scope['provider'] ?? [];
        $providerId = $provider['id'] ?? null;
        if (!$providerId) {
            return null;
        }

        $sharedBookingOfficeIds = null;
        $providerData = $provider['data'] ?? [];
        if (
            is_array($providerData)
            && isset($providerData['sharedBookingOfficeIds'])
            && is_array($providerData['sharedBookingOfficeIds'])
            && $providerData['sharedBookingOfficeIds'] !== []
        ) {
            $sharedBookingOfficeIds = array_map('intval', $providerData['sharedBookingOfficeIds']);
        }

        return [
            'scopeId' => $scopeId,
            'source' => $scope['source'] ?? 'dldb',
            'providerId' => $providerId,
            'sharedBookingOfficeIds' => $sharedBookingOfficeIds,
            'date' => $date,
            'slotCount' => isset($item['appointments__0__slotCount'])
                ? (int) $item['appointments__0__slotCount']
                : 1,
        ];
    }

    /**
     * @param array<string, mixed> $processInfo
     * @return array<string, mixed>
     */
    private function createMinimalProcess(array $processInfo): array
    {
        return [
            '$schema' => 'https://schema.berlin.de/queuemanagement/process.json',
            'scope' => [
                'id' => $processInfo['scopeId'],
                'source' => $processInfo['source'],
                'provider' => [
                    'id' => $processInfo['providerId'],
                    'source' => $processInfo['source'],
                ]
            ],
            'appointments' => [
                [
                    'date' => (string) $processInfo['date'],
                    'slotCount' => $processInfo['slotCount'] ?? 1,
                    'scope' => [
                        'id' => $processInfo['scopeId'],
                        'source' => $processInfo['source'],
                        'provider' => [
                            'id' => $processInfo['providerId'],
                            'source' => $processInfo['source'],
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * @return list<string>
     */
    private function parseCsv(string $value): array
    {
        if ($value === '') {
            return [];
        }

        return array_values(array_filter(
            array_map('trim', explode(',', $value)),
            static fn (string $item): bool => $item !== ''
        ));
    }

    /**
     * @return array{year: int, month: int, day: int}
     */
    private function datePartsFromIso(string $isoDate): array
    {
        $date = new \DateTimeImmutable($isoDate);

        return [
            'year' => (int) $date->format('Y'),
            'month' => (int) $date->format('n'),
            'day' => (int) $date->format('j'),
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $processList
     * @return array<string, mixed>
     */
    private function buildResult(
        BookingCalendar $calendar,
        array $processList,
        string $slotsStartDate,
        string $slotsEndDate,
        ?string $prevBookableDate,
        ?string $nextBookableDate,
        \DateTimeInterface $now
    ): array {
        $scopeToProvider = [];
        foreach ($calendar->scopes as $scope) {
            $scopeToProvider[(string) $scope['id']] = (string) $scope['provider']['id'];
        }

        $appointmentsByDateAndOffice = $this->groupAppointmentsByDateAndOffice($processList, $now);
        $days = [];

        foreach ($calendar->days as $day) {
            $dayData = $day;
            if (($dayData['status'] ?? null) !== 'bookable') {
                continue;
            }

            $date = sprintf(
                '%04d-%02d-%02d',
                (int) $dayData['year'],
                (int) $dayData['month'],
                (int) $dayData['day']
            );

            $inSlotsWindow = $date >= $slotsStartDate && $date <= $slotsEndDate;
            $dayAppointments = $appointmentsByDateAndOffice[$date] ?? [];

            if ($inSlotsWindow) {
                $providerIds = array_map('strval', array_keys($dayAppointments));
                sort($providerIds, SORT_STRING);
                if ($providerIds === []) {
                    continue;
                }
            } else {
                $scopeIdList = isset($dayData['scopeIDs']) && $dayData['scopeIDs'] !== ''
                    ? array_filter(explode(',', (string) $dayData['scopeIDs']))
                    : [];
                $providerIds = [];
                foreach ($scopeIdList as $scopeId) {
                    if (isset($scopeToProvider[$scopeId])) {
                        $providerIds[] = $scopeToProvider[$scopeId];
                    }
                }
                $providerIds = array_values(array_unique($providerIds));
                sort($providerIds, SORT_STRING);
                if ($providerIds === []) {
                    continue;
                }
                $dayAppointments = [];
            }

            $days[] = [
                'date' => $date,
                'providerIDs' => implode(',', $providerIds),
                'appointments' => $dayAppointments,
            ];
        }

        return [
            'startDate' => $this->formatCalendarDate($calendar->firstDay),
            'endDate' => $this->formatCalendarDate($calendar->lastDay),
            'slotsStartDate' => $slotsStartDate,
            'slotsEndDate' => $slotsEndDate,
            'prevBookableDate' => $prevBookableDate,
            'nextBookableDate' => $nextBookableDate,
            'days' => $days,
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $processList
     * @return array<string, array<string, array<int, int>>>
     */
    private function groupAppointmentsByDateAndOffice(array $processList, \DateTimeInterface $now): array
    {
        $grouped = [];
        $nowTimestamp = $now->getTimestamp();

        foreach ($processList as $process) {
            $officeId = (string) ($process['scope']['provider']['id'] ?? '');
            if ($officeId === '') {
                continue;
            }

            foreach ($process['appointments'] ?? [] as $appointment) {
                $timestamp = (int) ($appointment['date'] ?? 0);
                if ($timestamp <= $nowTimestamp) {
                    continue;
                }

                $date = date('Y-m-d', $timestamp);
                $grouped[$date][$officeId][] = $timestamp;
            }
        }

        foreach ($grouped as &$byOffice) {
            foreach ($byOffice as &$timestamps) {
                sort($timestamps);
                $timestamps = array_values(array_unique($timestamps));
            }
        }
        unset($byOffice, $timestamps);

        return $grouped;
    }

    /**
     * @param array<string, int|string>|null $date
     */
    private function formatCalendarDate(?array $date): string
    {
        return sprintf(
            '%04d-%02d-%02d',
            (int) ($date['year'] ?? 0),
            (int) ($date['month'] ?? 0),
            (int) ($date['day'] ?? 0)
        );
    }
}
