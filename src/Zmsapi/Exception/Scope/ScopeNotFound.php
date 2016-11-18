<?php

namespace BO\Zmsapi\Exception\Scope;

/**
 * example class to generate an exception
 */
class ScopeNotFound extends \Exception
{
    protected $code = 500;

    protected $message = 'Zu den angegebenen Daten konnte kein Standort gefunden werden.';
}
