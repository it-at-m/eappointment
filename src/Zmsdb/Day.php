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

    public function readByCalendar(\BO\Zmsentities\Calendar $calendar, \DateTimeInterface $now)
    {
        // We use a temporary table, so we can use create and insert on a readonly connection
        $this->getReader()->exec(Query\Day::QUERY_CREATE_TEMPORARY_SCOPELIST);
        $monthList = $calendar->getMonthList();
        foreach ($monthList as $month) {
            $dateTime = $month->getFirstDay();
            foreach ($calendar->scopes as $scope) {
                $slotsRequired = $calendar->scopes->getRequiredSlotsByScope($scope);
                $this->getReader()->perform(Query\Day::QUERY_INSERT_TEMPORARY_SCOPELIST, [
                    'scopeID' => $scope->id,
                    'year' => $dateTime->format('Y'),
                    'month' => $dateTime->format('m'),
                    'slotsRequired' => $slotsRequired,
                ]);
            }
        }
        //var_dump($this->getReader()->fetchAll('SELECT * FROM calendarscope'));
        $dayList = new \BO\Zmsentities\Collection\DayList();
        $dayData = $this->getReader()->fetchAll(
            Query\Day::QUERY_DAYLIST_JOIN,
            []
        );
        foreach ($dayData as $day) {
            $day = new \BO\Zmsentities\Day($day);
            $dayList->addEntity($day);
        }
        $this->getReader()->exec(Query\Day::QUERY_DROP_TEMPORARY_SCOPELIST);
        return $dayList;
    }

    public function writeTemporaryScopeList(
        \BO\Zmsentities\Collection\ScopeList $scopeList,
        $year,
        $month
    ) {
        foreach ($scopeList as $scope) {
            $this->getReader()->perform(Query\Day::QUERY_INSERT_TEMPORARY_SCOPELIST, [
                'scopeID' => $scope->id,
                'year' => $year,
                'month' => $month
            ]);
        }
        return $this;
    }
}
