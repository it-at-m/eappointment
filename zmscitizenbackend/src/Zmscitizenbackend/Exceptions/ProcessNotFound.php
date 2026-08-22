<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Exceptions;

class ProcessNotFound extends \Exception
{
    protected $code = 404;

    protected $message = 'Zu den angegebenen Daten konnte kein Termin gefunden werden.';

    public string $template = 'BO\\Zmscitizenbackend\\Exceptions\\ProcessNotFound';
}
