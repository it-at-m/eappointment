<?php

namespace BO\Zmsapi\Exception\Source;

/**
 * example class to generate an exception
 */
class SourceNotFound extends \Exception
{
    protected $code = 404;

    protected $message = 'Zu den angegebenen Daten konnte keine Quelle gefunden werden.';
}
