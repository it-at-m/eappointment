<?php

namespace BO\Zmsapi\Exception\Scope;

/**
 * example class to generate an exception
 */
class ScopeNotFound extends \Exception
{
    protected int $code = 404;

    protected string $message = 'Zu den angegebenen Daten konnte kein Standort gefunden werden.';
}
