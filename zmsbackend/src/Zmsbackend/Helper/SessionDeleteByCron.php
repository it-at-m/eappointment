<?php

namespace BO\Zmsbackend\Helper;

/**
 * @codeCoverageIgnore
 */
class SessionDeleteByCron
{
    public static function init($sessionName, $timeInterval)
    {
        $deleteInSeconds = $timeInterval * 60;
        $query = new \BO\Zmsbackend\Session\Service\Session();
        $query->deleteByTimeInterval($sessionName, $deleteInSeconds);
    }
}
