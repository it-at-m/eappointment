<?php

namespace BO\Zmsbackend\Process\Exception;

class ProcessNotCurrentDate extends \Exception
{
    protected $code = 404;
    protected $message = 'Der angegebene Termin liegt nicht am heutigen Tag und kann nicht aufgerufen werden.';

    public $data = [];
}
