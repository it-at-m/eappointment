<?php

namespace BO\Zmsbackend\Calendar\Service;

use BO\Zmsentities\Calendar as Entity;
use BO\Zmsentities\Collection\DayList;

/**
 * Combined calendar days + free appointment slots in a single optimised DB pass.
 *
 * @SuppressWarnings(Coupling)
 */
class CalendarAvailability extends \BO\Zmsbackend\Base
{
    /**
     * @return array<string, mixed>
     */
    public function readFromQuery(
        \DateTimeInterface $now,
        string $slotType,
        $slotsRequired,
        ?string $startDate,
        ?string $endDate,
        ?string $officeIds,
        ?string $serviceIds,
        ?string $serviceCounts = '',
        ?string $providerSource = null,
        ?string $requestSource = null,
        ?string $traceId = null,
        ?string $slotsStartDate = null,
        ?string $slotsEndDate = null
    ): array {
        if (!$startDate || !$endDate || !$officeIds || !$serviceIds) {
            throw new \BO\Zmsbackend\Slot\Exception\Calendar\InvalidAvailabilityInput(
                'startDate, endDate, officeId and serviceId are required'
            );
        }

        [$slotsStartDate, $slotsEndDate] = $this->resolveSlotsDateRange(
            $startDate,
            $endDate,
            $slotsStartDate,
            $slotsEndDate
        );

        $t0 = microtime(true);
        $calendar = $this->buildCalendarFromQuery(
            $startDate,
            $endDate,
            $officeIds,
            $serviceIds,
            $serviceCounts ?? '',
            $providerSource,
            $requestSource
        );
        $buildMs = (int) round((microtime(true) - $t0) * 1000);

        if (\App::$log) {
            \App::$log->info('calendar.availability.timing', [
                'trace_id' => $traceId,
                'stage' => 'backend.buildCalendarFromQuery',
                'ms' => $buildMs,
                'office_count' => count($calendar->providers ?? []),
                'request_count' => count($calendar->requests ?? []),
                'slots_start_date' => $slotsStartDate,
                'slots_end_date' => $slotsEndDate,
            ]);
        }

        return $this->readAvailability(
            $calendar,
            $now,
            $slotType,
            $slotsRequired,
            $traceId,
            $slotsStartDate,
            $slotsEndDate
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function readAvailability(
        Entity $calendar,
        \DateTimeInterface $now,
        string $slotType = 'public',
        $slotsRequired = 0,
        ?string $traceId = null,
        ?string $slotsStartDate = null,
        ?string $slotsEndDate = null
    ): array {
        $dayRangeStart = $this->formatCalendarDate($calendar->firstDay);
        $dayRangeEnd = $this->formatCalendarDate($calendar->lastDay);
        [$slotsStartDate, $slotsEndDate] = $this->resolveSlotsDateRange(
            $dayRangeStart,
            $dayRangeEnd,
            $slotsStartDate,
            $slotsEndDate
        );

        // Free-slot SQL only for slotsStart/End (may be a single day).
        // Bookable-day SQL only for the painted month of that window (not the full horizon).
        [$responseStartDate, $responseEndDate] = $this->resolveResponseDaysRange(
            $slotsStartDate,
            $slotsEndDate,
            $dayRangeStart,
            $dayRangeEnd
        );

        $t0 = microtime(true);
        $calendar = (new Calendar())->readResolvedEntity(
            $calendar,
            $now,
            true,
            $slotType,
            $slotsRequired,
            false,
            true,
            $traceId
        );
        $tAfterResolve = microtime(true);

        $dayQuery = new \BO\Zmsbackend\Day\Service\Day();
        $bookableDays = $this->readBookableDaysForRange(
            $calendar,
            $dayQuery,
            $slotsRequired,
            $slotType,
            $now,
            $responseStartDate,
            $responseEndDate,
            false
        );
        $tAfterDaySql = microtime(true);

        $slotDays = $this->filterDaysInDateRange($bookableDays, $slotsStartDate, $slotsEndDate);
        $responseDays = $this->filterDaysInDateRange($bookableDays, $responseStartDate, $responseEndDate);
        $calendar->days = $slotDays;
        $tAfterDays = microtime(true);

        $processList = [];
        if (count($slotDays) > 0) {
            $processList = (new \BO\Zmsbackend\Process\Service\ProcessStatusFree())
                ->readFreeProcessesMinimalFromPreparedCalendar(
                    $calendar,
                    $slotType,
                    $slotsRequired,
                    false,
                    $traceId
                );
        }
        $tAfterSlots = microtime(true);

        [$prevBookableDate, $nextBookableDate] = $this->findAdjacentBookableDatesByScan(
            $calendar,
            $dayQuery,
            $slotsRequired,
            $slotType,
            $now,
            $responseStartDate,
            $responseEndDate,
            $dayRangeStart,
            $dayRangeEnd
        );
        $tAfterNeighbors = microtime(true);

        $calendar->days = $responseDays;
        $result = $this->buildResult(
            $calendar,
            $processList,
            $slotsStartDate,
            $slotsEndDate,
            $prevBookableDate,
            $nextBookableDate
        );

        if (\App::$log) {
            \App::$log->info('calendar.availability.timing', [
                'trace_id' => $traceId,
                'stage' => 'backend.readAvailability',
                'resolve_ms' => (int) round(($tAfterResolve - $t0) * 1000),
                'day_sql_ms' => (int) round(($tAfterDaySql - $tAfterResolve) * 1000),
                'day_filter_ms' => (int) round(($tAfterDays - $tAfterDaySql) * 1000),
                'daylist_ms' => (int) round(($tAfterDays - $tAfterResolve) * 1000),
                'slots_ms' => (int) round(($tAfterSlots - $tAfterDays) * 1000),
                'neighbor_scan_ms' => (int) round(($tAfterNeighbors - $tAfterSlots) * 1000),
                'build_ms' => (int) round((microtime(true) - $tAfterNeighbors) * 1000),
                'total_ms' => (int) round((microtime(true) - $t0) * 1000),
                'scope_count' => count($calendar->scopes),
                'bookable_days' => count($bookableDays),
                'days_returned' => count($result['days'] ?? []),
                'slot_days_queried' => count($slotDays),
                'slots_start_date' => $slotsStartDate,
                'slots_end_date' => $slotsEndDate,
                'response_start_date' => $responseStartDate,
                'response_end_date' => $responseEndDate,
                'prev_bookable_date' => $prevBookableDate,
                'next_bookable_date' => $nextBookableDate,
                'process_count' => count($processList),
            ]);
        }

        return $result;
    }

    /**
     * Defaults slots window to the day range. When one side is missing, use the day range bound.
     * Clamps the slots window to the day range intersection.
     *
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
        } catch (\Exception $exception) {
            throw new \BO\Zmsbackend\Slot\Exception\Calendar\InvalidAvailabilityInput(
                'slotsStartDate and slotsEndDate must be valid dates (YYYY-MM-DD)'
            );
        }

        if ($slotsStart > $slotsEnd) {
            throw new \BO\Zmsbackend\Slot\Exception\Calendar\InvalidAvailabilityInput(
                'slotsStartDate must not be after slotsEndDate'
            );
        }

        // Clamp to day-status range so slot SQL never exceeds the resolved calendar.
        if ($slotsStart < $startDate) {
            $slotsStart = $startDate;
        }
        if ($slotsEnd > $endDate) {
            $slotsEnd = $endDate;
        }
        if ($slotsStart > $slotsEnd) {
            throw new \BO\Zmsbackend\Slot\Exception\Calendar\InvalidAvailabilityInput(
                'slots date range does not overlap startDate/endDate'
            );
        }

        return [$slotsStart, $slotsEnd];
    }

    /**
     * Bookable days returned to the client cover full calendar month(s) of the free-slots window
     * (clamped to the overall day-status range), so a single-day slot query still paints the month.
     *
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

    private function filterDaysInDateRange(DayList $days, string $startDate, string $endDate): DayList
    {
        $filtered = new DayList();
        foreach ($days as $day) {
            $date = $this->formatDayIso($day);
            if ($date >= $startDate && $date <= $endDate) {
                $filtered->addEntity($day);
            }
        }

        return $filtered;
    }

    /**
     * Run daylist for a date range only (calendarscope months derived from firstDay/lastDay).
     * Restores the calendar horizon afterwards so response startDate/endDate stay full-range.
     *
     * @param bool $rewrite When false, create the temp table (first paint). When true, drop+rebuild.
     */
    private function readBookableDaysForRange(
        Entity $calendar,
        \BO\Zmsbackend\Day\Service\Day $dayQuery,
        $slotsRequired,
        string $slotType,
        \DateTimeInterface $now,
        string $rangeStartDate,
        string $rangeEndDate,
        bool $rewrite
    ): DayList {
        $savedFirst = $calendar->firstDay;
        $savedLast = $calendar->lastDay;
        $calendar->firstDay = $this->datePartsFromIso($rangeStartDate);
        $calendar->lastDay = $this->datePartsFromIso($rangeEndDate);

        try {
            if ($rewrite) {
                $dayQuery->rewriteTemporaryScopeList($calendar, $slotsRequired);
            } else {
                $dayQuery->writeTemporaryScopeList($calendar, $slotsRequired);
            }
            $dayList = $dayQuery->readListFromPreparedTemporaryScopeList($slotsRequired)
                ->setStatusByType($slotType, $now)
                ->withDaysInDateRange($calendar->getFirstDay(), $calendar->getLastDay());
        } finally {
            $calendar->firstDay = $savedFirst;
            $calendar->lastDay = $savedLast;
        }

        $bookableDays = new DayList();
        foreach ($dayList as $day) {
            $status = is_array($day) ? ($day['status'] ?? null) : ($day['status'] ?? null);
            if ($status === 'bookable') {
                $bookableDays->addEntity($day);
            }
        }

        return $bookableDays;
    }

    /**
     * Walk neighbor months until the first bookable date outside the painted window is found.
     *
     * @return array{0: ?string, 1: ?string}
     */
    private function findAdjacentBookableDatesByScan(
        Entity $calendar,
        \BO\Zmsbackend\Day\Service\Day $dayQuery,
        $slotsRequired,
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
                $dayQuery,
                $slotsRequired,
                $slotType,
                $now,
                $responseStartDate,
                $dayRangeStart
            ),
            $this->findFirstBookableDateAfter(
                $calendar,
                $dayQuery,
                $slotsRequired,
                $slotType,
                $now,
                $responseEndDate,
                $dayRangeEnd
            ),
        ];
    }

    private function findFirstBookableDateAfter(
        Entity $calendar,
        \BO\Zmsbackend\Day\Service\Day $dayQuery,
        $slotsRequired,
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
                $dayQuery,
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
        Entity $calendar,
        \BO\Zmsbackend\Day\Service\Day $dayQuery,
        $slotsRequired,
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
                $dayQuery,
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

    private function formatDayIso(mixed $day): string
    {
        $dayData = $this->dayToArray($day);

        return sprintf(
            '%04d-%02d-%02d',
            (int) ($dayData['year'] ?? 0),
            (int) ($dayData['month'] ?? 0),
            (int) ($dayData['day'] ?? 0)
        );
    }

    private function buildCalendarFromQuery(
        string $startDate,
        string $endDate,
        string $officeIds,
        string $serviceIds,
        string $serviceCounts,
        ?string $providerSource,
        ?string $requestSource
    ): Entity {
        $calendar = new Entity();
        $calendar->firstDay = $this->datePartsFromIso($startDate);
        $calendar->lastDay = $this->datePartsFromIso($endDate);

        $officeIdList = $this->parseCsv($officeIds);
        $providerSources = $providerSource
            ? []
            : (new \BO\Zmsbackend\Provider\Service\Provider())->readSourceMapByIds($officeIdList);

        foreach ($officeIdList as $officeId) {
            $source = $providerSource ?: ($providerSources[$officeId] ?? null);
            if (!$source) {
                throw new \BO\Zmsbackend\Slot\Exception\Calendar\InvalidAvailabilityInput(
                    'Unknown officeId: ' . $officeId
                );
            }

            $calendar->providers[] = [
                'id' => (int) $officeId,
                'source' => $source,
            ];
        }

        $serviceIdList = $this->parseCsv($serviceIds);
        $countList = $this->parseCsv($serviceCounts);
        $requestSources = $requestSource
            ? []
            : (new \BO\Zmsbackend\Request\Service\Request())->readSourceMapByIds(
                array_values(array_filter(array_map('strval', $serviceIdList)))
            );

        foreach ($serviceIdList as $index => $serviceId) {
            $source = $requestSource ?: ($requestSources[$serviceId] ?? null);
            if (!$source) {
                throw new \BO\Zmsbackend\Slot\Exception\Calendar\InvalidAvailabilityInput(
                    'Unknown serviceId: ' . $serviceId
                );
            }

            $count = max(1, (int) ($countList[$index] ?? 1));
            if ($count > \BO\Zmsbackend\Slot\Service\Slot::MAX_SLOTS) {
                throw new \BO\Zmsbackend\Slot\Exception\Calendar\InvalidAvailabilityInput(
                    'serviceCount exceeds maximum of ' . \BO\Zmsbackend\Slot\Service\Slot::MAX_SLOTS
                );
            }
            for ($slot = 0; $slot < $count; $slot++) {
                $calendar->requests[] = [
                    'id' => $serviceId,
                    'source' => $source,
                ];
            }
        }

        return $calendar;
    }

    /**
     * @return array<int, string>
     */
    private function parseCsv(string $value): array
    {
        if ($value === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $value)), static fn (string $item): bool => $item !== ''));
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
     *
     * @return array<string, mixed>
     */
    private function buildResult(
        Entity $calendar,
        array $processList,
        string $slotsStartDate,
        string $slotsEndDate,
        ?string $prevBookableDate,
        ?string $nextBookableDate
    ): array {
        $scopeToProvider = [];
        foreach ($calendar->scopes as $scope) {
            $scopeToProvider[(string) $scope['id']] = (string) $scope['provider']['id'];
        }

        $appointmentsByDateAndOffice = $this->groupAppointmentsByDateAndOffice($processList);
        $days = [];

        foreach ($calendar->days as $day) {
            $dayData = $this->dayToArray($day);
            if (($dayData['status'] ?? null) !== 'bookable') {
                continue;
            }

            $date = sprintf(
                '%04d-%02d-%02d',
                (int) $dayData['year'],
                (int) $dayData['month'],
                (int) $dayData['day']
            );

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

            $dayAppointments = [];
            foreach ($providerIds as $providerId) {
                if (isset($appointmentsByDateAndOffice[$date][$providerId])) {
                    $dayAppointments[$providerId] = $appointmentsByDateAndOffice[$date][$providerId];
                }
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
     *
     * @return array<string, array<string, array<int, int>>>
     */
    private function groupAppointmentsByDateAndOffice(array $processList): array
    {
        $grouped = [];
        $now = time();

        foreach ($processList as $process) {
            $officeId = (string) ($process['scope']['provider']['id'] ?? '');
            if ($officeId === '') {
                continue;
            }

            foreach ($process['appointments'] ?? [] as $appointment) {
                $timestamp = (int) ($appointment['date'] ?? 0);
                if ($timestamp <= $now) {
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
     * @return array<string, mixed>
     */
    private function dayToArray(mixed $day): array
    {
        if ($day instanceof \BO\Zmsentities\Day) {
            return $day->getArrayCopy();
        }

        return (array) $day;
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
