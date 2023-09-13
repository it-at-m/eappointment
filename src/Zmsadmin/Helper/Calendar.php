<?php
/**
 *
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsadmin\Helper;

use \BO\Zmsentities\Calendar as Entity;
use \BO\Zmsentities\Day;
use \BO\Zmsentities\Collection\DayList;
use \BO\Zmsentities\Collection\ProcessList;

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
        } catch (\BO\Zmsclient\Exception $exception) {
            if ($exception->template != 'BO\Zmsapi\Exception\Calendar\AppointmentsMissed') {
                throw $exception;
            }
        }
        return $calendar->getMonthList();
    }

    public function readAvailableSlotsFromDayAndScopeList(
        \BO\Zmsentities\Collection\ScopeList $scopeList,
        $slotType = 'intern',
        $slotsRequired = 0
    ) {
        $this->calendar->scopes = $scopeList;
        $this->calendar->firstDay->setDateTime($this->dateTime);
        $this->calendar->lastDay->setDateTime($this->dateTime);
        try {
            $slots = \App::$http->readPostResult(
                '/process/status/free/',
                $this->calendar,
                [
                    'slotType' => $slotType,
                    'slotsRequired' => $slotsRequired,
                    'gql' => GraphDefaults::getFreeProcessList()
                ]
            )->getCollection();
        } catch (\BO\Zmsclient\Exception $exception) {
            if ($exception->template != 'BO\Zmsapi\Exception\Process\FreeProcessListEmpty') {
                throw $exception;
            }
        }
        return $slots;
    }

    public function readWeekDayListWithProcessList(\BO\Zmsentities\Collection\ScopeList $scopeList)
    {
        $dayList = new \BO\Zmsentities\Collection\DayList();
        $startDate = clone $this->dateTime->modify('Monday this week');
        $endDate = clone $this->dateTime->modify('Sunday this week');
        $currentDate = $startDate;
        while ($currentDate <= $endDate) {
            $day = (new Day)->setDateTime($currentDate);
            $day->status = Day::DETAIL;
            $processList = new \BO\Zmsentities\Collection\ProcessList();
            foreach ($scopeList as $scope) {
                $this->dateTime = $currentDate;
                if ($currentDate->format('Y-m-d') >= \App::$now->format('Y-m-d')) {
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
            }
            $day['processList'] = $this->toProcessListByHour($processList);
            $dayList->addEntity($day);
            $currentDate = $currentDate->modify('+1 day');
        }
        return $this->toDayListByHour($dayList);
    }

    protected function getDateTimeFromWeekAndYear($week, $year)
    {
        $dateTime = new \DateTimeImmutable();
        return $dateTime->setISODate($year, $week);
    }

    public function toProcessListByHour(ProcessList $processList)
    {
        $list = array();
        $oldList = clone $processList;
        $oldList->sortByArrivalTime();
        foreach ($oldList as $process) {
            if (in_array($process->status, [ 'confirmed', 'free'])) {
                $appointment = $process->getFirstAppointment();
                $hour = (int)$appointment->toDateTime()->format('H');
                if (!isset($list[$hour])) {
                    $list[$hour] = array();
                }
                if (!isset($list[$hour][intval($appointment['date'])])) {
                    $list[$hour][intval($appointment['date'])] = new ProcessList();
                }
                $list[$hour][intval($appointment['date'])]->addEntity(clone $process);
                ksort($list[$hour]);
            }
        }
        ksort($list);
        return $list;
    }

    public function toDayListByHour(DayList $dayList)
    {
        $list = array();
        $hours = array();
        $dayKeys = array();
        foreach ($dayList as $day) {
            $list['days'][] = $day;
            $dayKey = $day->year .'-'. $day->month .'-'. $day->day;
            $dayKeys[$dayKey] = $dayKey;
            foreach ($day['processList'] as $hour => $processList) {
                $list['hours'][$hour][$dayKey] = $processList;
                $hours[$hour] = $hour;
            }
        }
        foreach ($hours as $hour) {
            foreach ($dayKeys as $dayKey) {
                if (!isset($list['hours'][$hour][$dayKey])) {
                    $list['hours'][$hour][$dayKey] = new ProcessList();
                }
            }
            ksort($list['hours'][$hour]);
        }

        if (is_array($list['hours'])) {
            ksort($list['hours']);
        }

        return $list;
    }
}
