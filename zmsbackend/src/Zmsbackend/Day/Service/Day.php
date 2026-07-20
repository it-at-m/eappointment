<?php

namespace BO\Zmsbackend\Day\Service;

use BO\Zmsentities\Day as Entity;

/**
 *
 */
class Day extends \BO\Zmsbackend\Base
{
    protected $tempScopeListExists = false;

    public function writeTemporaryScopeList(\BO\Zmsentities\Calendar $calendar, $slotsRequiredForce = null)
    {
        $this->getReader()->exec(\BO\Zmsbackend\Day\Repository\Day::QUERY_CREATE_TEMPORARY_SCOPELIST);
        $monthList = $calendar->getMonthList();
        $slotsRequired = $slotsRequiredForce;
        foreach ($monthList as $month) {
            $dateTime = $month->getFirstDay();
            foreach ($calendar->scopes as $scope) {
                if (!$slotsRequiredForce) {
                    $slotsRequired = $calendar->scopes->getRequiredSlotsByScope($scope);
                }
                $this->getReader()->perform(\BO\Zmsbackend\Day\Repository\Day::QUERY_INSERT_TEMPORARY_SCOPELIST, [
                    'scopeID' => $scope->id,
                    'year' => $dateTime->format('Y'),
                    'month' => $dateTime->format('m'),
                    'slotsRequired' => $slotsRequired > 1 ? round($slotsRequired, 0) : 1,
                ]);
            }
        }
        $this->tempScopeListExists = true;
    }

    /**
     * Drop and rebuild calendarscope for the calendar's current firstDay/lastDay months.
     * Used to shrink the daylist window (painted month) or scan one neighbor month at a time.
     */
    public function rewriteTemporaryScopeList(\BO\Zmsentities\Calendar $calendar, $slotsRequiredForce = null)
    {
        if ($this->tempScopeListExists) {
            $this->getReader()->exec(\BO\Zmsbackend\Day\Repository\Day::QUERY_DROP_TEMPORARY_SCOPELIST);
            $this->tempScopeListExists = false;
        }
        $this->writeTemporaryScopeList($calendar, $slotsRequiredForce);
    }

    public function readByCalendar(\BO\Zmsentities\Calendar $calendar, $slotsRequiredForce = null)
    {
        // We use a temporary table, so we can use create and insert on a readonly connection
        $this->writeTemporaryScopeList($calendar, $slotsRequiredForce);

        return $this->readListFromPreparedTemporaryScopeList($slotsRequiredForce);
    }

    public function readListFromPreparedTemporaryScopeList($slotsRequiredForce = null)
    {
        $dayList = new \BO\Zmsentities\Collection\DayList();
        $dayData = $this->getReader()->fetchAll(
            \BO\Zmsbackend\Day\Repository\Day::QUERY_DAYLIST_JOIN,
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
