<?php
/**
 *
 * @package Events\Xmas
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsadmin\Helper;

use \BO\Zmsentities\Calendar;

class PageCalendar
{
    protected $calendar;

    protected $dateTime;

    public function __construct($selectedDate = null)
    {
        $this->dateTime = ($selectedDate) ? new \BO\Zmsentities\Helper\DateTime($selectedDate) : \App::$now;
        $this->calendar = new Calendar();
        $this->calendar->firstDay->setDateTime($this->dateTime->modify('first day of this month'));
        $this->calendar->lastDay->setDateTime($this->dateTime->modify('last day of next month'));
    }

    public function readByScope(\BO\Zmsentities\Scope $scope)
    {
        $this->calendar->scopes[] = $scope;
        try {
            $calendar = \App::$http->readPostResult('/calendar/', $this->calendar, ['fillWithEmptyDays' => 1])->getEntity();
            return $calendar->getMonthList();
        } catch (\BO\Zmsclient\Exception $exception) {
            if ($exception->template != 'BO\Zmsapi\Exception\Calendar\AppointmentsMissed') {
                throw $exception;
            }
            // TODO Berechne die Tage im Kalendar
        }
    }
}
