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
use BO\Zmsentities\Scope;

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
        $this->calendar->firstDay = new Day();
        $this->calendar->lastDay = new Day();
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
        $startDate = clone $this->dateTime->modify('Monday this week');
        $endDate = clone $this->dateTime->modify('Sunday this week');
        $this->calendar->firstDay->setDateTime($startDate);
        $this->calendar->lastDay->setDateTime($endDate);

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

    public function readWeekDayListWithProcessList(
        ?\BO\Zmsentities\Cluster $cluster,
        \BO\Zmsentities\Workstation $workstation
    ) {
        $showAllInCluster = $cluster && 1 == $workstation->queue['clusterEnabled'];
        $scopeList = $workstation->getScopeList($cluster);
        $scope = $scopeList->getFirst();

        $dayList = new \BO\Zmsentities\Collection\DayList();

        if ($showAllInCluster) {
            $bookedProcessList = \App::$http
                ->readGetResult('/cluster/'. $cluster->id .'/process/'. $this->dateTime->format('Y-m-d') . '/', ['showWeek' => 1])
                ->getCollection();
        } else {
            $bookedProcessList = \App::$http
                ->readGetResult('/scope/'. $scope->id .'/process/'. $this->dateTime->format('Y-m-d') . '/', ['showWeek' => 1])
                ->getCollection();
        }

        /** @var ProcessList $bookedProcessList */
        $processListByDate = $this->splitByDate($bookedProcessList);

        $startDate = clone $this->dateTime->modify('Monday this week');
        $endDate = clone $this->dateTime->modify('Sunday this week');
        $currentDate = $startDate;

        /** @var ProcessList $freeProcessList */
        $freeProcessList = $this->readAvailableSlotsFromDayAndScopeList($scopeList);
        $freeProcessListByDate = $freeProcessList ? $this->splitByDate($freeProcessList) : [];
        var_dump($freeProcessListByDate);

        while ($currentDate <= $endDate) {
            $day = (new Day)->setDateTime($currentDate);
            $day->status = Day::DETAIL;
            $processList = new \BO\Zmsentities\Collection\ProcessList();

            $this->dateTime = $currentDate;
            if ($currentDate->format('Y-m-d') >= \App::$now->format('Y-m-d')) {
                if (isset($freeProcessListByDate[$currentDate->format('Y-m-d')])) {
                    $processList->addList($freeProcessListByDate[$currentDate->format('Y-m-d')]);
                }

                if (isset($processListByDate[$currentDate->format('Y-m-d')])) {
                    $processList->addList($processListByDate[$currentDate->format('Y-m-d')]);
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
        $oldList = $processList;
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
                $list[$hour][intval($appointment['date'])]->addEntity($process);

                unset($process);
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

    private function splitByDate(ProcessList $processList)
    {
        $list = [];

        foreach ($processList as $process) {
            $dayKey = $process->getFirstAppointment()->toDateTime()->format("Y-m-d");
            if (! isset($list[$dayKey])) {
                $list[$dayKey] = new \BO\Zmsentities\Collection\ProcessList();;
            }

            $list[$dayKey]->addEntity($process);
        }

        return $list;
    }
}
