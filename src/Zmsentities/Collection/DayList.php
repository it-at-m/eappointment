<?php
namespace BO\Zmsentities\Collection;

class DayList extends Base
{
    public function getDay($year, $month, $dayNumber)
    {
        foreach ($this as $day) {
            $day = new \BO\Zmsentities\Day($day);
            if ($day->year == $year && $day->month == $month && $day->day == $dayNumber) {
                return $day;
            }
        }
        return new \BO\Zmsentities\Day([
            'year' => $year,
            'month' => $month,
            'day' => $dayNumber
        ]);
    }

    public function hasDay($year, $month, $dayNumber)
    {
        $result = false;
        foreach ($this as $day) {
            $day = new \BO\Zmsentities\Day($day);
            if ($day->year == $year && $day->month == $month && $day->day == $dayNumber) {
                $result = true;
            }
        }
        return $result;
    }

    public function withAssociatedDays($month)
    {
        $dayList = new self();
        foreach ($this as $day) {
            $day = new \BO\Zmsentities\Day($day);
            if ($day->month == $month) {
                $dayList->addEntity($day);
            }
        }
        return $dayList;
    }
}
