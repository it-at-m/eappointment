<?php
/**
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

    public static function hashQueryParameters(array $args, array $parameters, string $hashFunction = 'md5')
    {
        $content = '';
        foreach ($parameters as $parameter) {
            if (isset($args[$parameter])) {
                if (is_array($args[$parameter])) {
                    array_walk_recursive(
                        $args[$parameter],
                        function ($v) use (&$flat) {
                            $flat[] = strval($v);
                        }
                    );
                    $content .= implode('', $flat);
                } else {
                    $content .= (string) $args[$parameter];
                }
            } else {
                $content .= 'NULL';
            }
        }

        return $hashFunction($content . \App::$urlSignatureKey);
    }
}
