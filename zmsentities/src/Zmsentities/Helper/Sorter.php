<?php

namespace BO\Zmsentities\Helper;

/**
 * Special sort algorithm for DLDB
 */
class Sorter
{
    /**
     * @todo check against ISO definition
     */
    public static function toSortableString($string)
    {
        $string = strtr($string, array(
            'Ä' => 'Ae',
            'Ö' => 'Oe',
            'Ü' => 'Ue',
            'ä' => 'ae',
            'ö' => 'oe',
            'ü' => 'ue',
            'ß' => 'ss',
            '€' => 'E',
        ));
        return $string;
    }

    public static function toSortedCsv($csvString)
    {
        $csvElements = explode(',', $csvString);
        sort($csvElements);
        return implode(',', $csvElements);
    }
}
