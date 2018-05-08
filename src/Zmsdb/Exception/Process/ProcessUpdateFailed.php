<?php

namespace BO\Zmsdb\Exception\Process;

class ProcessUpdateFailed extends \Exception
{
    protected $code = 500;

    protected $message = 'Failed to update process.';
}
