<?php

namespace BO\Zmsbackend\Helper;

/**
 * @codeCoverageIgnore
 */
class DayoffDeleteByCron
{
    public static function init($timeInterval)
    {
        $deleteInSeconds = (365 * 24 * 60 * 60 / 12) * $timeInterval;
        $query = new \BO\Zmsbackend\Dayoff\Service\DayOff();
        $query->deleteByTimeInterval($deleteInSeconds);
    }
}
