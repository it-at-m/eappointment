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
        $dateTime = Helper\DateTime::create($this['year'] .'-'. $this['month'] .'-1');
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
            $this->days[$key] = $dayList->getDayByDay($day);
        }
        return $this;
    }
}
