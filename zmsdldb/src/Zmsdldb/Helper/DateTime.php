<?php

namespace BO\Zmsdldb\Helper;

class DateTime extends \DateTimeImmutable
{
    public static function getFormatedDates(
        $timestamp,
        $pattern = 'MMMM',
        $locale = 'de_DE',
        $timezone = 'Europe/Berlin'
    ) {
        return \BO\Mellon\IntlDateFormat::format((int) $timestamp, $pattern, $locale, $timezone);
    }

    public function __toString()
    {
        return $this->format('c');
    }
}
