<?php

namespace BO\Zmsentities;

class Month extends Schema\Entity
{
    const PRIMARY = 'month';

    public $calendarDayList;

    public static $schema = "month.json";

    public function getFirstDay()
    {
        $dateTime = Helper\DateTime::create($this['year'] .'-'. $this['month'] .'-1');
        return $dateTime->modify('00:00:00');
    }

    public function getWithStatedDayList(\DateTimeInterface $now)
    {
        $dayList = new Collection\DayList($this->days);
        $uniqueDayList = new Collection\DayList();
        $this->appointmentExists = false;
        $startDate = $this->getFirstDay();
        for ($dayNumber = 1; $dayNumber <= $startDate->format('t'); $dayNumber ++) {
            $day = str_pad($dayNumber, 2, '0', STR_PAD_LEFT);
            $dayEntity = $dayList->getDay($this->year, $this->month, $day);
            if ($dayEntity->toDateTime() >= $now && $dayList->hasDay($this->year, $this->month, $day)) {
                $dayEntity->getWithStatus();
            } else {
                $dayEntity->status = 'notBookable';
            }
            $this->appointmentExists = ($dayEntity->isBookable() && !$this->appointmentExists) ? true : false;
            $uniqueDayList->addEntity($dayEntity);
        }
        $this->days = $uniqueDayList;
        return $this;
    }
}
