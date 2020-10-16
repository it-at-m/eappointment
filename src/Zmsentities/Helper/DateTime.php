<?php

namespace BO\Zmsentities\Helper;

class DateTime extends \DateTimeImmutable
{
    public static function create($time = 'now', \DateTimeZone $timezone = null)
    {
        if ($time instanceof \BO\Zmsentities\Helper\DateTime) {
            $dateTime = $time;
            if (null !== $timezone) {
                $dateTime = $dateTime->setTimezone($timezone);
            }
        } elseif ($time instanceof \DateTimeInterface) {
            $dateTime = new self();
            if (null !== $timezone) {
                $dateTime = $dateTime->setTimezone($timezone);
            } else {
                $dateTime = $dateTime->setTimezone($time->getTimezone());
            }
            $dateTime = $dateTime->setTimestamp($time->getTimestamp());
        } else {
            $dateTime = new self($time, $timezone);
        }
        return $dateTime;
    }

    public function getWeekOfMonth()
    {
        // Todo: This is correct way of calculating week of month by date, but zms1 has 1-7 = 1, 8-14 = 2,...
        /*
        $week = $this->format('W');
        $firstWeekOfMonth = $this->modify('first day of this month')->format('W');
        return 1 + ($week < $firstWeekOfMonth ? $week : $week - $firstWeekOfMonth);
        */

        $dayOfMonth = $this->format('j');
        $weekOfMonth = ceil($dayOfMonth / 7);
        return $weekOfMonth;
    }

    public function isWeekOfMonth($number)
    {
        return (int)$this->getWeekOfMonth() === (int)$number;
    }

    public function isLastWeekOfMonth()
    {
        $weekOfMonth = $this->getWeekOfMonth();
        $lastDay = $this->modify('last day of this month');
        return $weekOfMonth == $lastDay->getWeekOfMonth();
    }

    public function getSecondsOfDay()
    {
        $hours = $this->format('G');
        $minutes = $this->format('i');
        $seconds = $this->format('s');
        return $hours * 3600 + $minutes * 60 + $seconds;
    }

    public function __toString()
    {
        return $this->format('c');
    }
}
