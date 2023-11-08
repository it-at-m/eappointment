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

    
    public static function hashQueryParameters(
        string $section,
        array $queryVariables,
        array $parameters,
        string $hashFunction = 'md5'
    ) {
        $content = $section . self::getContentForHash($queryVariables, $parameters);
        $hashString = $hashFunction($content . \App::$urlSignatureSecret);
        $firstHalf  = substr($hashString, 0, floor(strlen($hashString) / 2));
        $secondHalf = substr($hashString, strlen($firstHalf));
        $alphabet   = '0123456789' . implode(range('A', 'Z')) . implode(range('a', 'z'));
        $rotation   = 31;
        // reducing the hash to half its length by combining first half and second half
        for ($i = 0; $i < strlen($firstHalf); $i++) {
            $rotation = (strpos($alphabet, $firstHalf[$i]) + ord($secondHalf[$i]) + $rotation) % strlen($alphabet);
            $firstHalf[$i] = $alphabet[$rotation];
        }
        return $firstHalf;
    }

    protected static function getContentForHash(array $queryVariables, array $parameters)
    {
        $content = '';
        foreach ($parameters as $parameter) {
            if (isset($queryVariables[$parameter])) {
                if (is_iterable($queryVariables[$parameter])) {
                    $parameterArray = $queryVariables[$parameter];
                    ksort($parameterArray);
                    $flat = [];
                    array_walk_recursive(
                        $parameterArray,
                        function ($value) use (&$flat) {
                            $flat[] = strval($value);
                        }
                    );
                    $content .= implode('', $flat);
                } else {
                    $content .= (string) $queryVariables[$parameter];
                }
            } else {
                $content .= 'NULL';
            }
        }
        return $content;
    }
}
