<?php

namespace BO\Zmsbackend\Session\Exception;

/**
 * example class to generate an exception
 */
class SessionNotFound extends \Exception
{
    protected $code = 404;

    protected $message = 'Zu den angegebenen Daten konnte keine Session gefunden werden.';
}
