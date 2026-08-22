<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Exceptions;

class ProcessNotReservedAnymore extends \Exception
{
    protected $code = 404;

    protected $message = 'Failed to confirm process. Maybe time of reservation went out.';

    public string $template = 'BO\\Zmscitizenbackend\\Exceptions\\ProcessNotReservedAnymore';
}
