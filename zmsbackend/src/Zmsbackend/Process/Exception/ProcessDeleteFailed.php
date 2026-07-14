<?php

namespace BO\Zmsbackend\Process\Exception;

/**
 * example class to generate an exception
 */
class ProcessDeleteFailed extends \Exception
{
    protected $code = 404;

    protected $message = 'Failed to delete process. Please try again.';
}
