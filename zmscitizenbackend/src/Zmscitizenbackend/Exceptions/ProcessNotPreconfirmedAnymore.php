<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Exceptions;

class ProcessNotPreconfirmedAnymore extends \Exception
{
    protected $code = 404;

    protected $message = 'Failed to confirm process. Maybe time of preconformation went out.';

    public string $template = 'BO\\Zmscitizenbackend\\Exceptions\\ProcessNotPreconfirmedAnymore';
}
