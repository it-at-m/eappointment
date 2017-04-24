<?php

namespace BO\Zmsdb\Helper;

/**
 * @codeCoverageIgnore
 */
class AppointmentDeleteByCron
{
    public static function init($timeInterval)
    {
        $deleteInSeconds = (24 * 60 * 60) * $timeInterval;
        $query = new \BO\Zmsdb\Process();
        $query->deleteByTimeInterval($deleteInSeconds);
    }
}
