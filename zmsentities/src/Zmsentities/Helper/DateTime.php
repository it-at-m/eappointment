<?php

namespace BO\Zmsentities\Helper;

class DateTime extends \DateTimeImmutable implements \JsonSerializable
{
    public static function create(string|\DateTimeInterface|false $time = 'now', \DateTimeZone $timezone = null): self
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
            $dateTime = new self($time === false ? 'now' : $time, $timezone);
        }
        return $dateTime;
    }

    public function getWeekOfMonth(): float
    {
        // Todo: This is correct way of calculating week of month by date, but zms1 has 1-7 = 1, 8-14 = 2,...
        /*
        $week = $this->format('W');
        $firstWeekOfMonth = $this->modify('first day of this month')->format('W');
        return 1 + ($week < $firstWeekOfMonth ? $week : $week - $firstWeekOfMonth);
        */

        $dayOfMonth = (int) $this->format('j');
        $weekOfMonth = ceil($dayOfMonth / 7);
        return $weekOfMonth;
    }

    public function isWeekOfMonth(mixed $number): bool
    {
        return (int)$this->getWeekOfMonth() === (int)$number;
    }

    public function isLastWeekOfMonth(): bool
    {
        $weekOfMonth = $this->getWeekOfMonth();
        $lastDay = $this->modify('last day of this month');
        return $weekOfMonth == $lastDay->getWeekOfMonth();
    }

    public function getSecondsOfDay(): int|float
    {
        $hours = (int) $this->format('G');
        $minutes = (int) $this->format('i');
        $seconds = (int) $this->format('s');
        return $hours * 3600 + $minutes * 60 + $seconds;
    }

    /**
     *
     * @return false|string
     */
    public static function getFormatedDates(
        \DateTimeInterface $date,
        string $pattern = 'MMMM',
        string $locale = 'de_DE',
        string $timezone = 'Europe/Berlin'
    ): string|false {
        $dateFormatter = new \IntlDateFormatter(
            $locale,
            \IntlDateFormatter::MEDIUM,
            \IntlDateFormatter::MEDIUM,
            $timezone,
            \IntlDateFormatter::GREGORIAN,
            $pattern
        );
        return $dateFormatter->format($date->getTimestamp());
    }

    public static function getSummerTimeStartDateTime(mixed $year = null): \DateTime
    {
        $year = ($year) ? $year : date('Y');
        $dateTimeMarch = new \DateTime($year . '-03-01', new \DateTimeZone('Europe/Berlin'));
        $lastSunday = $dateTimeMarch->modify('Last Sunday of March');
        return $lastSunday->setTime(2, 0, 0);
    }

    public static function getSummerTimeEndDateTime(mixed $year = null): \DateTime
    {
        $year = ($year) ? $year : date('Y');
        $dateTimeOctober = new \DateTime($year . '-10-01', new \DateTimeZone('Europe/Berlin'));
        $lastSunday = $dateTimeOctober->modify('Last Sunday of October');
        return $lastSunday->setTime(3, 0, 0);
    }

    public function __toString(): string
    {
        return $this->format(DATE_ATOM);
    }

    #[\Override]
    public function jsonSerialize(): string
    {
        return $this->format(DATE_ATOM);
    }
}
