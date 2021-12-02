<?php

namespace BO\Zmsapi\Exception\Process;

class ProcessAlreadyExists extends \Exception
{
    protected $code = 404;

    protected $message = 'Failed to reserve an existing process.';
}
