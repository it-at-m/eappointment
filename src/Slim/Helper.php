<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Slim;

class Helper
{
    public static function proxySanitizeUri($uri)
    {
        $uri = str_replace(':80/', '/', $uri);
        return $uri;
    }

    public static function getFormatedDates(
        $timestamp,
        $pattern = 'MMMM',
        $locale = 'de_DE',
        $timezone = 'Europe/Berlin'
    ) {
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
}
