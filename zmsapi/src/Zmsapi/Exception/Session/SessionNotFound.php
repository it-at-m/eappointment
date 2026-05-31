<?php

namespace BO\Zmsapi\Exception\Session;

/**
 * example class to generate an exception
 */
class SessionNotFound extends \Exception
{
    protected int $code = 404;

    protected string $message = 'Zu den angegebenen Daten konnte keine Session gefunden werden.';
}
