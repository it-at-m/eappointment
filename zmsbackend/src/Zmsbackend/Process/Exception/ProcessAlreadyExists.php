<?php

namespace BO\Zmsbackend\Process\Exception;

class ProcessAlreadyExists extends \Exception
{
    protected $code = 404;

    protected $message = 'Failed to reserve an existing process.';
}
