<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Appointment\Exception;

class ProcessNotFound extends \Exception
{
    protected $code = 404;

    protected $message = 'Zu den angegebenen Daten konnte kein Termin gefunden werden.';

    public string $template = 'BO\\Zmscitizenbackend\\Appointment\\Exception\\ProcessNotFound';
}
