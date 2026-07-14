<?php

namespace BO\Zmsbackend\Process\Exception;

class ProcessAlreadyCalled extends \Exception
{
    protected $code = 404;

    protected $message = 'Failed to call process. It is already called by another workstation.';

    public array $data = [];
}
