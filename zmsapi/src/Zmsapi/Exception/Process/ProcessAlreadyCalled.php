<?php

namespace BO\Zmsapi\Exception\Process;

class ProcessAlreadyCalled extends \Exception
{
    protected $code = 404;

    protected $message = 'Failed to call process. It is already called by another workstation.';
}
