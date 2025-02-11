<?php

namespace BO\Zmsentities;

class Month extends Schema\Entity
{
    const PRIMARY = 'month';

    public $calendarDayList;

    public static $schema = "month.json";

    public function getDefaults()
    {
        return [
            'days' => new Collection\DayList(),
        ];
    }

    public function getFirstDay()
    {
        $dateTime = Helper\DateTime::create($this['year'] . '-' . $this['month'] . '-1');
        return $dateTime->modify('00:00:00');
    }

    public function getDayList()
    {
        if (!$this->days instanceof Collection\DayList) {
            $this->days = new Collection\DayList($this->days);
        }
        return $this->days;
    }

    public function setDays(Collection\DayList $dayList)
    {
        foreach ($this->getDayList() as $key => $day) {
            if (!$day instanceof Day) {
                $day = new Day($day);
            }
            $this->days[$key] = $dayList->getDayByDay($day);
        }
        return $this;
    }

    public static function createForDateFromDayList(
        \DateTimeInterface $currentDate,
        \BO\Zmsentities\Collection\DayList $dayList
    ) {
        $startDow = date('w', mktime(0, 0, 0, $currentDate->format('m'), 1, $currentDate->format('Y')));
        $monthDayList = $dayList->withAssociatedDays($currentDate);
        $month = (new self(
            array(
                'year' => $currentDate->format('Y'),
                'month' => $currentDate->format('m'),
                'calHeadline' => \BO\Zmsentities\Helper\DateTime::getFormatedDates($currentDate, 'MMMM yyyy'),
                'startDow' => ($startDow == 0) ? 6 : $startDow - 1, // change for week start with monday on 0,
                'days' => $monthDayList,
                'appointmentExists' => $monthDayList->hasDayWithAppointments(),
            )
        ));
        return $month;
    }
}
