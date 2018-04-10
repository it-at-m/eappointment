<?php
namespace BO\Zmsdb;

use \BO\Zmsentities\Day as Entity;

/**
 *
 */
class Day extends Base
{

    public function readDayListByMonth(
        $scopeID,
        $year,
        $month,
        $slotsRequired = 1
    ) {
        $sql = Query\Day::QUERY_DAYLIST;
        $data = $this->getReader()
            ->fetchAll(
                $sql,
                [
                    'scopeID' => $scopeID,
                    'year'    => $year,
                    'month'   => $month,
                    'slotsRequired' => $slotsRequired,
                ]
            );
        $dayList = new \BO\Zmsentities\Collection\DayList($data);
        return $dayList;
    }

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
                    'slotsRequired' => $slotsRequired ? round($slotsRequired, 0) : 1,
                ]);
            }
        }
    }

    public function readByCalendar(\BO\Zmsentities\Calendar $calendar, $slotsRequiredForce = null)
    {
        // We use a temporary table, so we can use create and insert on a readonly connection
        $this->writeTemporaryScopeList($calendar, $slotsRequiredForce);
        //var_dump($this->getReader()->fetchAll('SELECT * FROM calendarscope'));
        $dayList = new \BO\Zmsentities\Collection\DayList();
        $dayData = $this->getReader()->fetchAll(
            Query\Day::QUERY_DAYLIST_JOIN,
            []
        );
        foreach ($dayData as $day) {
            $day = new \BO\Zmsentities\Day($day);
            $dayList[$day->getDayHash()] = $day;
        }
        $this->getReader()->exec(Query\Day::QUERY_DROP_TEMPORARY_SCOPELIST);
        return $dayList;
    }
}
