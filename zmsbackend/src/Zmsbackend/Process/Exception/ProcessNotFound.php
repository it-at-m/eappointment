<?php

namespace BO\Zmsbackend\Process\Exception;

class ProcessNotFound extends \Exception
{
    protected $code = 404;
    protected $message = 'Zu den angegebenen Daten konnte kein Termin gefunden werden.';

    public $data = [];
}
