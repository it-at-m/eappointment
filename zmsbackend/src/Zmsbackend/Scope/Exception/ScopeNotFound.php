<?php

namespace BO\Zmsbackend\Scope\Exception;

/**
 * example class to generate an exception
 */
class ScopeNotFound extends \Exception
{
    protected $code = 404;

    protected $message = 'Zu den angegebenen Daten konnte kein Standort gefunden werden.';
}
