<?php

namespace BO\Zmsdb\Helper;

/**
 * @codeCoverageIgnore
 */
class DayoffDeleteByCron
{
    public static function init($timeInterval)
    {
        $deleteInSeconds = (365 * 24 * 60 * 60 / 12) * $timeInterval;
        $query = new \BO\Zmsdb\DayOff();
        $query->deleteByTimeInterval($deleteInSeconds);
    }
}
