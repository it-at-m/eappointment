<?php

namespace BO\Zmsapi\Exception\Process;

class ProcessNotCurrentDate extends \Exception
{
    protected int $code = 404;
    protected string $message = 'Der angegebene Termin liegt nicht am heutigen Tag und kann nicht aufgerufen werden.';

    /**
     * @var array
     */
    public array $data = [];
}
