<?php

namespace BO\Zmsdb\Exception\Process;

class ProcessAlreadyCalled extends \Exception
{
    protected $error = 404;

    protected $message = 'Failed to call process. It is already called by another workstation.';
}
