<?php
/**
 *
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsadmin\Helper;

use \BO\Zmsentities\Calendar as Entity;

class Calendar
{
    protected $calendar;

    protected $dateTime;

    public function __construct($selectedDate = null)
    {
        $this->dateTime = ($selectedDate) ? new \BO\Zmsentities\Helper\DateTime($selectedDate) : \App::$now;
        $this->calendar = new Entity();
    }

    public function readMonthListByScope(\BO\Zmsentities\Scope $scope)
    {
        $this->calendar->addScope($scope->id);
        $this->calendar->firstDay->setDateTime($this->dateTime->modify('first day of this month'));
        $this->calendar->lastDay->setDateTime($this->dateTime->modify('last day of next month'));
        try {
            $calendar = \App::$http->readPostResult(
                '/calendar/',
                $this->calendar,
                ['fillWithEmptyDays' => 1]
            )->getEntity();
            return $calendar->getMonthList();
        } catch (\BO\Zmsclient\Exception $exception) {
            if ($exception->template != 'BO\Zmsapi\Exception\Calendar\AppointmentsMissed') {
                throw $exception;
            }
            // TODO Berechne die Tage im Kalendar
        }
    }

    public function readAvailableSlotsFromDayAndScope(\BO\Zmsentities\Scope $scope)
    {
        $this->calendar->addScope($scope->id);
        $this->calendar->firstDay->setDateTime($this->dateTime);
        $this->calendar->lastDay->setDateTime($this->dateTime);
        try {
            return \App::$http->readPostResult('/process/status/free/', $this->calendar)->getCollection();
        } catch (\BO\Zmsclient\Exception $exception) {
            if ($exception->template != 'BO\Zmsapi\Exception\Process\FreeProcessListEmpty') {
                throw $exception;
            }
            // TODO Berechne die Tage im Kalendar
        }
    }
}
