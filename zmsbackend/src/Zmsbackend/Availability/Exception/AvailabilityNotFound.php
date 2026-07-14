<?php

namespace BO\Zmsbackend\Availability\Exception;

class AvailabilityNotFound extends \Exception
{
    protected $code = 404;

    protected $message = 'Zu den angegebenen Daten konnte keine Öffnungszeit gefunden werden.';
}
