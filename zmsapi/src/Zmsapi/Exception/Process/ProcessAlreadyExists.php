<?php

namespace BO\Zmsapi\Exception\Process;

class ProcessAlreadyExists extends \Exception
{
    protected int $code = 404;

    protected string $message = 'Failed to reserve an existing process.';
}
