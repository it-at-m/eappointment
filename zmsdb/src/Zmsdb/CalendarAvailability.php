<?php

namespace BO\Zmsdb;

use BO\Zmsentities\Calendar as Entity;
use BO\Zmsentities\Collection\DayList;

/**
 * Combined calendar days + free appointment slots in a single optimised DB pass.
 *
 * @SuppressWarnings(Coupling)
 */
class CalendarAvailability extends Base
{
    /**
     * @param array{
     *     startDate: string,
     *     endDate: string,
     *     officeIds: array<int|string>,
     *     serviceIds: array<int|string>,
     *     serviceCounts?: array<int|string>,
     *     providerSource?: string|null,
     *     requestSource?: string|null
     * } $params
     *
     * @return array<string, mixed>
     */
    public function readFromParams(
        array $params,
        \DateTimeInterface $now,
        string $slotType = 'public',
        $slotsRequired = 0
    ): array {
        return $this->readAvailability(
            $this->buildCalendarFromParams($params),
            $now,
            $slotType,
            $slotsRequired
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function readAvailability(
        Entity $calendar,
        \DateTimeInterface $now,
        string $slotType = 'public',
        $slotsRequired = 0
    ): array {
        $calendar = (new Calendar())->readResolvedEntity(
            $calendar,
            $now,
            true,
            $slotType,
            $slotsRequired,
            false
        );

        $dayQuery = new Day();
        $dayQuery->writeTemporaryScopeList($calendar, $slotsRequired);
        $dayList = $dayQuery->readListFromPreparedTemporaryScopeList($calendar, $slotsRequired);
        $calendar->days = $dayList
            ->setStatusByType($slotType, $now)
            ->withDaysInDateRange($calendar->getFirstDay(), $calendar->getLastDay());

        $bookableDays = new DayList();
        foreach ($calendar->days as $day) {
            $status = is_array($day) ? ($day['status'] ?? null) : ($day['status'] ?? null);
            if ($status === 'bookable') {
                $bookableDays->addEntity($day);
            }
        }
        $calendar->days = $bookableDays;

        $processList = (new ProcessStatusFree())->readFreeProcessesMinimalFromPreparedCalendar(
            $calendar,
            $slotType,
            $slotsRequired,
            false
        );

        return $this->buildResult($calendar, $processList);
    }

    /**
     * @param array{
     *     startDate: string,
     *     endDate: string,
     *     officeIds: array<int|string>,
     *     serviceIds: array<int|string>,
     *     serviceCounts?: array<int|string>,
     *     providerSource?: string|null,
     *     requestSource?: string|null
     * } $params
     */
    private function buildCalendarFromParams(array $params): Entity
    {
        $calendar = new Entity();
        $calendar->firstDay = $this->datePartsFromIso($params['startDate']);
        $calendar->lastDay = $this->datePartsFromIso($params['endDate']);

        $providerSource = $params['providerSource'] ?? null;
        $providerSources = $providerSource
            ? []
            : (new Provider())->readSourceMapByIds($params['officeIds']);

        foreach ($params['officeIds'] as $officeId) {
            $officeId = (string) $officeId;
            $source = $providerSource ?: ($providerSources[$officeId] ?? null);
            if (!$source) {
                throw new Exception\Calendar\InvalidAvailabilityInput('Unknown officeId: ' . $officeId);
            }

            $calendar->providers[] = [
                'id' => (int) $officeId,
                'source' => $source,
            ];
        }

        $requestSource = $params['requestSource'] ?? null;
        $uniqueServiceIds = array_values(array_filter(array_map('strval', $params['serviceIds'])));
        $requestSources = $requestSource
            ? []
            : (new Request())->readSourceMapByIds($uniqueServiceIds);
        $serviceCounts = $params['serviceCounts'] ?? [];

        foreach ($params['serviceIds'] as $index => $serviceId) {
            $serviceId = (string) $serviceId;
            if ($serviceId === '') {
                continue;
            }

            $source = $requestSource ?: ($requestSources[$serviceId] ?? null);
            if (!$source) {
                throw new Exception\Calendar\InvalidAvailabilityInput('Unknown serviceId: ' . $serviceId);
            }

            $count = max(1, (int) ($serviceCounts[$index] ?? 1));
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
    private function buildResult(Entity $calendar, array $processList): array
    {
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
