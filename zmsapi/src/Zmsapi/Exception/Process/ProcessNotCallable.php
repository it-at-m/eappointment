<?php

namespace BO\Zmsapi\Exception\Process;

class ProcessNotCallable extends \Exception
{
    protected int $code = 403;
    protected string $message = 'Der angegebene Termin kann in seinem aktuellen Status nicht aufgerufen werden.';

    /**
     * @var array
     */
    public array $data = [];
}
