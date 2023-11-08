<?php

namespace BO\Zmsdb\Helper;

/**
 * @codeCoverageIgnore
 */
class SessionDeleteByCron
{
    public static function init($sessionName, $timeInterval)
    {
        $deleteInSeconds = $timeInterval * 60;
        $query = new \BO\Zmsdb\Session();
        $query->deleteByTimeInterval($sessionName, $deleteInSeconds);
    }
}
