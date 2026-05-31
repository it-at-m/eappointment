<?php

namespace BO\Zmsapi\Exception\Availability;

class AvailabilityNotFound extends \Exception
{
    protected int $code = 404;

    protected string $message = 'Zu den angegebenen Daten konnte keine Öffnungszeit gefunden werden.';
}
