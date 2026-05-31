<?php

namespace BO\Zmsapi\Exception\Source;

/**
 * example class to generate an exception
 */
class SourceNotFound extends \Exception
{
    protected int $code = 404;

    protected string $message = 'Zu den angegebenen Daten konnte keine Quelle gefunden werden.';
}
