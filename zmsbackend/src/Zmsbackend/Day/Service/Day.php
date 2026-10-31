<?php

namespace BO\Zmsbackend\Day\Service;

use BO\Zmsentities\Day as Entity;

class Day extends \BO\Zmsbackend\Base
{
    protected $tempScopeListExists = false;

    public function writeTemporaryScopeList(\BO\Zmsentities\Calendar $calendar, int|float|null $slotsRequiredForce = null): void
    {
        $this->getReader()->exec(\BO\Zmsbackend\Day\Repository\Day::QUERY_CREATE_TEMPORARY_SCOPELIST);
        $intervals = $this->readAppointmentIntervalByScope($calendar);
        $monthList = $calendar->getMonthList();
        $slotsRequired = $slotsRequiredForce;
        foreach ($monthList as $month) {
            $dateTime = $month->getFirstDay();
            foreach ($calendar->scopes as $scope) {
                if (!$slotsRequiredForce) {
                    $slotsRequired = $calendar->scopes->getRequiredSlotsByScope($scope);
                }
                $scopeId = (int) $scope->id;
                $this->getReader()->perform(\BO\Zmsbackend\Day\Repository\Day::QUERY_INSERT_TEMPORARY_SCOPELIST, [
                    'scopeID' => $scopeId,
                    'year' => $dateTime->format('Y'),
                    'month' => $dateTime->format('m'),
                    'slotsRequired' => $slotsRequired > 1 ? round($slotsRequired, 0) : 1,
                    'appointmentInterval' => $intervals[$scopeId] ?? 5,
                ]);
            }
        }
        $this->tempScopeListExists = true;
    }

    /**
     * @return array<int, int>
     */
    private function readAppointmentIntervalByScope(\BO\Zmsentities\Calendar $calendar): array
    {
        $scopeIds = [];
        foreach ($calendar->scopes as $scope) {
            $scopeIds[] = (int) $scope->id;
        }
        $scopeIds = array_values(array_unique(array_filter($scopeIds)));
        $intervals = [];
        foreach ($scopeIds as $scopeId) {
            $intervals[$scopeId] = 5;
        }
        if ($scopeIds === []) {
            return $intervals;
        }

        $rows = $this->fetchAll(
            'SELECT sc.`StandortID` AS scopeId,
                    rp.`slots` AS slots,
                    CAST(JSON_UNQUOTE(JSON_EXTRACT(p.`data`, \'$.slotTimeInMinutes\')) AS UNSIGNED) AS minutes
             FROM `standort` sc
             INNER JOIN `provider` p
                 ON p.`id` = sc.`InfoDienstleisterID`
                 AND p.`source` = sc.`source`
             INNER JOIN `request_provider` rp
                 ON rp.`provider__id` = p.`id`
                 AND rp.`source` = p.`source`
                 AND rp.`slots` > 0
             WHERE sc.`StandortID` IN (' . implode(',', $scopeIds) . ')'
        );
        $durations = [];
        foreach ($rows as $row) {
            $minutes = (int) $row['minutes'];
            $slots = (int) $row['slots'];
            if ($minutes < 1 || $slots < 1) {
                continue;
            }
            $durations[(int) $row['scopeId']][] = $slots * $minutes;
        }
        foreach ($durations as $scopeId => $scopeDurations) {
            $intervals[$scopeId] = $this->appointmentInterval($scopeDurations);
        }

        return $intervals;
    }

    /**
     * @param list<int> $durationMinutes
     */
    private function appointmentInterval(array $durationMinutes): int
    {
        $interval = 0;
        foreach ($durationMinutes as $duration) {
            $duration = (int) $duration;
            if ($duration < 1) {
                continue;
            }
            $interval = $interval === 0 ? $duration : $this->greatestCommonDivisor($interval, $duration);
        }
        if ($interval >= 5 && $interval % 5 === 0) {
            return $interval;
        }

        return 5;
    }

    private function greatestCommonDivisor(int $a, int $b): int
    {
        while ($b !== 0) {
            $remainder = $a % $b;
            $a = $b;
            $b = $remainder;
        }

        return abs($a);
    }

    /**
     * Drop and rebuild calendarscope for the calendar's current firstDay/lastDay months.
     * Used to shrink the daylist window (painted month) or scan one neighbor month at a time.
     */
    public function rewriteTemporaryScopeList(\BO\Zmsentities\Calendar $calendar, int|float|null $slotsRequiredForce = null): void
    {
        if ($this->tempScopeListExists) {
            $this->getReader()->exec(\BO\Zmsbackend\Day\Repository\Day::QUERY_DROP_TEMPORARY_SCOPELIST);
            $this->tempScopeListExists = false;
        }
        $this->writeTemporaryScopeList($calendar, $slotsRequiredForce);
    }

    public function readByCalendar(\BO\Zmsentities\Calendar $calendar, int|float|null $slotsRequiredForce = null)
    {
        // We use a temporary table, so we can use create and insert on a readonly connection
        $this->writeTemporaryScopeList($calendar, $slotsRequiredForce);

        return $this->readListFromPreparedTemporaryScopeList($slotsRequiredForce);
    }

    public function readListFromPreparedTemporaryScopeList(int|float|null $slotsRequiredForce = null, ?string $daylistQuery = null): \BO\Zmsentities\Collection\DayList
    {
        $dayList = new \BO\Zmsentities\Collection\DayList();
        $dayData = $this->getReader()->fetchAll(
            $daylistQuery ?? \BO\Zmsbackend\Day\Repository\Day::QUERY_DAYLIST_JOIN,
            [
                'forceRequiredSlots' =>
                    ($slotsRequiredForce === null || $slotsRequiredForce < 1) ? 1 : round($slotsRequiredForce),
            ]
        );
        foreach ($dayData as $day) {
            $day = new \BO\Zmsentities\Day($day);
            $dayList[$day->getDayHash()] = $day;
        }

        return $dayList;
    }

    /**
     * Remove temporary scope list at destruct to allow other functions to use it
     */
    public function __destruct()
    {
        if ($this->tempScopeListExists) {
            $this->getReader()->exec(\BO\Zmsbackend\Day\Repository\Day::QUERY_DROP_TEMPORARY_SCOPELIST);
        }
    }
}
