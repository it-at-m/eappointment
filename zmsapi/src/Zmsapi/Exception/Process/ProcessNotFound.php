<?php

namespace BO\Zmsapi\Exception\Process;

class ProcessNotFound extends \Exception
{
    protected int $code = 404;
    protected string $message = 'Zu den angegebenen Daten konnte kein Termin gefunden werden.';

    /**
     * @var array
     */
    public array $data = [];
}
