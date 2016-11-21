<?php
namespace BO\Zmsentities\Collection;

use \BO\Zmsentities\Day;

class DayList extends Base implements JsonUnindexed
{
    /**
     * ATTENTION: Performance critical, keep highly optimized
     *
     */
    public function getDay($year, $month, $dayNumber)
    {
        $dateHash = "$dayNumber-$month-$year";
        if (array_key_exists($dateHash, $this)) {
            return $this[$dateHash];
        }
        foreach ($this as $key => $day) {
            if (!$day instanceof Day) {
                $day = new Day($day);
                $this[$key] = $day;
            }
            if ($day->day == $dayNumber && $day->month == $month && $day->year == $year) {
                unset($this[$key]);
                $this[$dateHash] = $day;
                return $day;
            }
        }
        $day  = new \BO\Zmsentities\Day([
            'year' => $year,
            'month' => $month,
            'day' => $dayNumber
        ]);
        $this[$dateHash] = $day;
        return $day;
    }

    public function getDayByDateTime(\DateTimeInterface $datetime)
    {
        return $this->getDay($datetime->format('Y'), $datetime->format('m'), $datetime->format('d'));
    }

    public function getDayByDay(\BO\Zmsentities\Day $day)
    {
        return $this->getDay($day->year, $day->month, $day->day);
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
        return $dayList->sortByCustomKey('day');
    }

    public function setSort($property = 'day')
    {
        $this->uasort(function ($dayA, $dayB) use ($property) {
            return strnatcmp($dayA[$property], $dayB[$property]);
        });
        return $this;
    }
}
