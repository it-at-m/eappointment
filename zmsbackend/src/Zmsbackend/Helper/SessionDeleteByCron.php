<?php

namespace BO\Zmsbackend\Helper;

/**
 * @codeCoverageIgnore
 */
class SessionDeleteByCron
{
    /**
     * Delete sessiondata older than SESSION_DURATION.
     * When $sessionName is null/empty, all session names are cleaned.
     */
    public static function init(?string $sessionName = null, ?int $deleteInSeconds = null): void
    {
        $deleteInSeconds = $deleteInSeconds ?? (int) \App::SESSION_DURATION;
        $query = new \BO\Zmsbackend\Session\Service\Session();
        $query->deleteByTimeInterval($sessionName, $deleteInSeconds);
    }
}
