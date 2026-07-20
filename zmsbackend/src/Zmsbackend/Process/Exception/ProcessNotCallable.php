<?php

namespace BO\Zmsbackend\Process\Exception;

class ProcessNotCallable extends \Exception
{
    protected $code = 403;
    protected $message = 'Der angegebene Termin kann in seinem aktuellen Status nicht aufgerufen werden.';

    public $data = [];
}
