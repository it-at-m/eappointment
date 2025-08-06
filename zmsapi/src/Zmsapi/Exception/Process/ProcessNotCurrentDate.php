<?php

namespace BO\Zmsapi\Exception\Process;

class ProcessNotCurrentDate extends \Exception
{
    protected $code = 404;
    protected $message = 'Der angegebene Termin liegt nicht am heutigen Tag und kann nicht aufgerufen werden.';

    public $data = [];
}
