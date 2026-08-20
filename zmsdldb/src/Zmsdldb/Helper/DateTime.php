<?php

namespace BO\Zmsdldb\Helper;

class DateTime extends \DateTimeImmutable
{
    /**
     * @return false|string
     */
    public static function getFormatedDates(
        \DateTimeImmutable $timestamp,
        string $pattern = 'MMMM',
        $locale = 'de_DE',
        $timezone = 'Europe/Berlin'
    ): string|false {
        $dateFormatter = new \IntlDateFormatter(
            $locale,
            \IntlDateFormatter::MEDIUM,
            \IntlDateFormatter::MEDIUM,
            $timezone,
            \IntlDateFormatter::GREGORIAN,
            $pattern
        );
        return $dateFormatter->format($timestamp);
    }

    public function __toString()
    {
        return $this->format('c');
    }
}
