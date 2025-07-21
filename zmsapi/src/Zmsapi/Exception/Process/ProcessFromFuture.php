<?php

namespace BO\Zmsapi\Exception\Process;

class ProcessFromFuture extends \Exception
{
    protected $code = 404;
    protected $message = 'Der angegebene Termin liegt in der Zukunft und kann nicht aufgerufen werden.';

    public $data = [];
}
