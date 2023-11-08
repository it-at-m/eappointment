<?php

namespace BO\Zmsapi\Exception\Availability;

class AvailabilityNotFound extends \Exception
{
    protected $code = 404;

    protected $message = 'Zu den angegebenen Daten konnte keine Öffnungszeit gefunden werden.';
}
