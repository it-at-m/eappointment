<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Appointment\Exception;

class ProcessDeleteFailed extends \Exception
{
    protected $code = 404;

    protected $message = 'Failed to delete process. Please try again.';

    public string $template = 'BO\\Zmscitizenbackend\\Appointment\\Exception\\ProcessDeleteFailed';
}
