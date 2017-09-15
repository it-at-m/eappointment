<?php
/**
 *
 * @package Zmsstatistic
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsstatistic\Helper;

use \BO\Zmsentities\Calendar as Entity;

class Calendar
{
    protected $calendar;

    protected $dateTime;

    public function __construct($selectedDate = null, $selectedWeek = null, $selectedYear = null)
    {
        if ($selectedWeek && $selectedYear) {
            $this->dateTime = $this->getDateTimeFromWeekAndYear($selectedWeek, $selectedYear);
        } else {
            $this->dateTime = ($selectedDate) ? new \BO\Zmsentities\Helper\DateTime($selectedDate) : \App::$now;
        }

        $this->calendar = new Entity();
    }

    public function getDateTime()
    {
        return $this->dateTime;
    }

    public function readMonthListByScopeList(\BO\Zmsentities\Collection\ScopeList $scopeList, $slotType, $slotsRequired)
    {
        // TODO Berechne die Tage im Kalendar
        $this->calendar->scopes = $scopeList;
        $this->calendar->firstDay->setDateTime($this->dateTime->modify('first day of this month'));
        $this->calendar->lastDay->setDateTime($this->dateTime->modify('last day of next month'));
        try {
            $calendar = \App::$http->readPostResult(
                '/calendar/',
                $this->calendar,
                [
                    'fillWithEmptyDays' => 1,
                    'slotType' => $slotType,
                    'slotsRequired' => $slotsRequired
                ]
            )->getEntity();
            return $calendar->getMonthList();
        } catch (\BO\Zmsclient\Exception $exception) {
            if ($exception->template != 'BO\Zmsapi\Exception\Calendar\AppointmentsMissed') {
                throw $exception;
            }
        }
    } // @codeCoverageIgnore

    public function readAvailableSlotsFromDayAndScopeList(
        \BO\Zmsentities\Collection\ScopeList $scopeList,
        $slotType = 'intern',
        $slotsRequired = 0
    ) {
        $this->calendar->scopes = $scopeList;
        $this->calendar->firstDay->setDateTime($this->dateTime);
        $this->calendar->lastDay->setDateTime($this->dateTime);
        try {
            return \App::$http->readPostResult(
                '/process/status/free/',
                $this->calendar,
                [
                    'slotType' => $slotType,
                    'slotsRequired' => $slotsRequired
                ]
            )->getCollection();
        } catch (\BO\Zmsclient\Exception $exception) {
            if ($exception->template != 'BO\Zmsapi\Exception\Process\FreeProcessListEmpty') {
                throw $exception;
            }
        }
    } // @codeCoverageIgnore

    public function readWeekDayListWithProcessList(\BO\Zmsentities\Collection\ScopeList $scopeList)
    {
        $dayList = new \BO\Zmsentities\Collection\DayList();
        $startDate = clone $this->dateTime->modify('this week');
        $endDate = clone $this->dateTime->modify('+6 days');
        $currentDate = $startDate;
        while ($currentDate <= $endDate) {
            $day = (new \BO\Zmsentities\Day)->setDateTime($currentDate);
            $processList = new \BO\Zmsentities\Collection\ProcessList();
            foreach ($scopeList as $scope) {
                $this->dateTime = $currentDate;
                $freeProcessList = $this->readAvailableSlotsFromDayAndScopeList($scopeList);
                $bookedProcessList = \App::$http
                    ->readGetResult('/scope/'. $scope->id .'/process/'. $currentDate->format('Y-m-d') .'/')
                    ->getCollection();
                if ($bookedProcessList) {
                    $processList->addList($bookedProcessList);
                }
                if ($freeProcessList) {
                    $processList->addList($freeProcessList);
                }
            }
            $day['processList'] = $processList->toProcessListByStatusAndTime();
            $dayList->addEntity($day);
            $currentDate = $currentDate->modify('+1 day');
        }
        return $dayList;
    }

    protected function getDateTimeFromWeekAndYear($week, $year)
    {
        $dateTime = new \DateTimeImmutable();
        return $dateTime->setISODate($year, $week);
    }
}
