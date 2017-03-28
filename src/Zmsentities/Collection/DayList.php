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

    public function getMonthIndex()
    {
        $daysByMonth = array();
        foreach ($this as $day) {
            $day = new Day($day);
            $daysByMonth[$day->toDateTime()->format('m')][] = $day;
        }
        return array_keys($daysByMonth);
    }

    public function withAssociatedDays($currentDate)
    {
        $dayList = new self();
        foreach ($this->getMonthIndex() as $monthIndex) {
            if ($currentDate->format('m') == $monthIndex) {
                for ($dayNumber = 1; $dayNumber <= $currentDate->format('t'); $dayNumber ++) {
                    $day = str_pad($dayNumber, 2, '0', STR_PAD_LEFT);
                    $entity = $this->getDay($currentDate->format('Y'), $currentDate->format('m'), $day);
                    $dayList->addEntity($entity);
                }
            }
        }
        return $dayList->sortByCustomKey('day');
    }

    /*
     * There is a collection function sortByCustomKey, that does the same !!!
     *
     */
    public function setSort($property = 'day')
    {
        $this->uasort(function ($dayA, $dayB) use ($property) {
            return strnatcmp($dayA[$property], $dayB[$property]);
        });
        return $this;
    }

    public function hasDayWithAppointments()
    {
        foreach ($this as $hash => $day) {
            $hash = null;
            $day = new Day($day);
            if ($day->isBookable()) {
                return true;
            }
        }
        return false;
    }

    public function toSortedByHour()
    {
        $list = array();
        foreach ($this as $day) {
            $list['days'][] = $day;
            $dayKey = $day->year .'-'. $day->month .'-'. $day->day;
            foreach ($day['processList'] as $hour => $processList) {
                $list['hours'][$hour][$dayKey] = $processList;
            }
        }
        return $list;
    }
}
