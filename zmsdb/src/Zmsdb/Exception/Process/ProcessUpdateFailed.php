<?php

namespace BO\Zmsbackend\Process\Exception;

class ProcessUpdateFailed extends \Exception
{
    protected $code = 500;

    protected $message = 'Failed to update process.';
}
