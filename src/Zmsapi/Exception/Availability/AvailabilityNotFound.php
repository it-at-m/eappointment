<?php

namespace BO\Zmsapi\Exception\Availability;

/**
 * example class to generate an exception
 */
class AvailabilityNotFound extends \Exception
{
    protected $code = 404;

    protected $message = 'Zu den angegebenen Daten konnte keine Öffnungszeit gefunden werden.';
}
