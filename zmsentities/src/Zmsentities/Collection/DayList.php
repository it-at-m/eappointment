<?php

namespace BO\Zmsentities\Collection;

use BO\Zmsentities\Day;

class DayList extends Base implements JsonUnindexed
{
    const ENTITY_CLASS = '\BO\Zmsentities\Day';

    /**
     * ATTENTION: Performance critical, keep highly optimized
     *
     */
    public function getDay($year, $month, $dayNumber, $createDay = true)
    {
        $dateHash = Day::getCalculatedDayHash($dayNumber, $month, $year);
        if ($this->offsetExists($dateHash)) {
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
        if ($createDay) {
            $day  = new \BO\Zmsentities\Day([
                'year' => $year,
                'month' => $month,
                'day' => $dayNumber
            ]);
            $this[$dateHash] = $day;
            return $day;
        }
        return null;
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
        $day = $this->getDay($year, $month, $dayNumber, false);
        return ($day === null) ? false : true;
    }

    public function withAssociatedDays($currentDate)
    {
        $dayList = new self();
        $lastDay = $currentDate->format('t');
        for ($dayNumber = 1; $dayNumber <= $lastDay; $dayNumber++) {
            $day = str_pad($dayNumber, 2, '0', STR_PAD_LEFT);
            $entity = $this->getDay($currentDate->format('Y'), $currentDate->format('m'), $day);
            $dayList->addEntity($entity);
        }
        return $dayList->sortByCustomKey('day');
    }

    public function setStatusByType($slotType, \DateTimeInterface $dateTime)
    {
        foreach ($this as $day) {
            $day->getWithStatus($slotType, $dateTime);
        }
        return $this;
    }

    public function withAddedDayList(DayList $dayList)
    {
        $merged = new DayList();
        foreach ($dayList as $day) {
            // @codeCoverageIgnoreStart
            if (!$day instanceof Day) {
                $day = new Day($day);
            }
            // @codeCoverageIgnoreEnd
            $merged->addEntity($day->withAddedDay($this->getDayByDay($day)));
        }
        return $merged;
    }

    public function setSortByDate()
    {
        $this->uasort(function ($dayA, $dayB) {
            return (
                intval($dayA['year'] . $dayA['month'] . $dayA['day']) -
                intval($dayB['year'] . $dayB['month'] . $dayB['day'])
            );
        });
        return $this;
    }

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
            if ($day->hasAppointments()) {
                return true;
            }
        }
        return false;
    }

    public function getFirstBookableDay()
    {
        foreach ($this as $day) {
            $day = new Day($day);
            if ($day->isBookable()) {
                return $day->toDateTime();
            }
        }
        return null;
    }

    public function withDaysInDateRange(\DateTimeInterface $startDate, \DateTimeInterface $endDate)
    {
        $list = new self();
        foreach ($this as $day) {
            if (
                $day->toDateTime() >= $startDate->modify('00:00:00') &&
                $day->toDateTime() <= $endDate->modify('23:59:59')
            ) {
                $list->addEntity($day);
            }
        }
        return $list;
    }

    public function withDaysFromPeriod(\DateTimeInterface $startDate, \DateTimeInterface $endDate)
    {
        $list = new self();
        do {
            $day = (new Day())->setDateTime($startDate);
            $list->addEntity($day);
            $startDate = $startDate->modify('+1 day');
        } while ($startDate <= $endDate);
        return $list;
    }
}
