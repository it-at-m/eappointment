<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Exceptions;

class ProcessDeleteFailed extends \Exception
{
    protected $code = 404;

    protected $message = 'Failed to delete process. Please try again.';

    public string $template = 'BO\\Zmscitizenbackend\\Exceptions\\ProcessDeleteFailed';
}
