<?php

namespace BO\Zmsdb\Exception\Process;

class ProcessCreateFailed extends \Exception
{
    protected $code = 500;

    protected $message = 'Failed to create process.';
}
