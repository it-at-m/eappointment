<?php

namespace BO\Zmsapi\Exception\Process;

/**
 * example class to generate an exception
 */
class ProcessDeleteFailed extends \Exception
{
    protected int $code = 404;

    protected string $message = 'Failed to delete process. Please try again.';
}
