<?php

namespace BO\Zmsapi\Exception\Process;

class ProcessAlreadyCalled extends \Exception
{
    protected int $code = 404;

    protected string $message = 'Failed to call process. It is already called by another workstation.';

    public array $data = [];
}
