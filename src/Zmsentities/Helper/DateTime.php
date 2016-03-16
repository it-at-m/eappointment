<?php

namespace BO\Zmsentities\Helper;

class DateTime extends \DateTimeImmutable
{
    public static function create($time = 'now', \DateTimeZone $timezone = null)
    {
        if ($time instanceof \DateTimeInterface) {
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

    public function getWeeks()
    {
        $timestamp = $this->getTimestamp();
        $days = $timestamp / 86400;
        $weeks = $days / 7;
        return $weeks;
    }

    public function getWeekOfMonth()
    {
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
}
