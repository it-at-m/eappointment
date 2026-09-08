<?php

/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Slim;

class Helper
{
    /**
     * @param null|string|string[] $uri
     *
     *
     * @return string|string[]
     *
     */
    public static function proxySanitizeUri(array|string|null $uri): array|string
    {
        if ($uri === null) {
            return '';
        }
        $uri = str_replace(':80/', '/', $uri);
        return $uri;
    }

    /**
     * @param \DateTimeImmutable|int $timestamp
     *
     * @return false|string
     */
    public static function getFormatedDates(
        int|\DateTimeImmutable $timestamp,
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
        return $dateFormatter->format($timestamp);
    }


    public static function hashQueryParameters(
        string $section,
        array $queryVariables,
        array $parameters,
        string $hashFunction = 'md5'
    ): string {
        $content = $section . self::getContentForHash($queryVariables, $parameters);
        $hashString = $hashFunction($content . \App::$urlSignatureSecret);
        $halfLength = (int) floor(strlen($hashString) / 2);
        $firstHalf  = substr($hashString, 0, $halfLength);
        $secondHalf = substr($hashString, strlen($firstHalf));
        $alphabet   = '0123456789' . implode(range('A', 'Z')) . implode(range('a', 'z'));
        $rotation   = 31;
        // reducing the hash to half its length by combining first half and second half
        for ($i = 0; $i < strlen($firstHalf); $i++) {
            $position = strpos($alphabet, $firstHalf[$i]);
            $rotation = (($position === false ? 0 : $position) + ord($secondHalf[$i]) + $rotation) % strlen($alphabet);
            $firstHalf[$i] = $alphabet[$rotation];
        }
        return $firstHalf;
    }

    protected static function getContentForHash(array $queryVariables, array $parameters): string
    {
        $content = '';
        foreach ($parameters as $parameter) {
            if (isset($queryVariables[$parameter])) {
                if (is_array($queryVariables[$parameter])) {
                    $parameterArray = $queryVariables[$parameter];
                    ksort($parameterArray);
                    $flat = [];
                    array_walk_recursive(
                        $parameterArray,
                        function (mixed $value) use (&$flat) {
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
