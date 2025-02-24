<?php

namespace BO\Zmsapi\Helper;

use BO\Slim\Render;

/**
 *
 * @SuppressWarnings(CouplingBetweenObjects)
 */
class ExchangePeriod
{
    protected static $year = '';

    protected static $month = '';

    protected static $day = '';

    protected static $periodIdentifier = 'day';

    protected static $startDateTime = null;

    protected static $endDateTime = null;

    public function __construct($period)
    {
        $periodArr = preg_split('%(-)%', $period);
        static::$year = $periodArr[0];
        static::$month = (isset($periodArr[1])) ? $periodArr[1] : '';
        static::$day = (isset($periodArr[2])) ? $periodArr[2] : '';
    }

    public function getStartDateTime()
    {
        if ('_' == static::$year) {
            static::$periodIdentifier = 'hour';
            static::$startDateTime = \App::$now;
        } elseif (static::$year && static::$month && static::$day) {
            static::$periodIdentifier = 'hour';
            static::$startDateTime = new \DateTimeImmutable(static::$year . '-' . static::$month . '-' . static::$day);
        } elseif (static::$year && static::$month && ! static::$day) {
            static::$periodIdentifier = 'day';
            static::$startDateTime = new \DateTimeImmutable(static::$year . '-' . static::$month . '-01');
        } elseif (! static::$month) {
            static::$periodIdentifier = 'month';
            static::$startDateTime = new \DateTimeImmutable(static::$year . '-01-01');
        }
        return static::$startDateTime;
    }

    public function getEndDateTime()
    {
        if ('hour' == static::$periodIdentifier) {
            static::$endDateTime = static::$startDateTime;
        } elseif ('day' == static::$periodIdentifier) {
            static::$endDateTime = static::$startDateTime->modify('last day of this month');
        } elseif ('month' == static::$periodIdentifier) {
            static::$endDateTime = new \DateTimeImmutable(static::$year . '-12-31');
        }
        return static::$endDateTime;
    }

    public function getPeriodIdentifier($groupby)
    {
        return ($groupby) ? $groupby : static::$periodIdentifier;
    }
}
