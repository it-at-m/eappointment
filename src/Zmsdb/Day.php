<?php
namespace BO\Zmsdb;

use \BO\Zmsentities\Day as Entity;

/**
 *
 */
class Day extends Base
{

    protected $tempScopeListExists = false;

    public function writeTemporaryScopeList(\BO\Zmsentities\Calendar $calendar, $slotsRequiredForce = null)
    {
        $this->getReader()->exec(Query\Day::QUERY_CREATE_TEMPORARY_SCOPELIST);
        $monthList = $calendar->getMonthList();
        $slotsRequired = $slotsRequiredForce;
        foreach ($monthList as $month) {
            $dateTime = $month->getFirstDay();
            foreach ($calendar->scopes as $scope) {
                if (!$slotsRequiredForce) {
                    $slotsRequired = $calendar->scopes->getRequiredSlotsByScope($scope);
                }
                $this->getReader()->perform(Query\Day::QUERY_INSERT_TEMPORARY_SCOPELIST, [
                    'scopeID' => $scope->id,
                    'year' => $dateTime->format('Y'),
                    'month' => $dateTime->format('m'),
                    'slotsRequired' => $slotsRequired > 1 ? round($slotsRequired, 0) : 1,
                ]);
            }
        }
        $this->tempScopeListExists = true;
    }

    public function readByCalendar(\BO\Zmsentities\Calendar $calendar, $slotsRequiredForce = null)
    {
        // We use a temporary table, so we can use create and insert on a readonly connection
        $this->writeTemporaryScopeList($calendar, $slotsRequiredForce);
        //var_dump($this->getReader()->fetchAll('SELECT * FROM calendarscope'));
        $dayList = new \BO\Zmsentities\Collection\DayList();
        $dayData = $this->getReader()->fetchAll(
            Query\Day::QUERY_DAYLIST_JOIN,
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
            $this->getReader()->exec(Query\Day::QUERY_DROP_TEMPORARY_SCOPELIST);
        }
    }
}
