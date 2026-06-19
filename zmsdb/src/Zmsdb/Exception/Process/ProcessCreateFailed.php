<?php

namespace BO\Zmsbackend\Process\Exception;

class ProcessCreateFailed extends \Exception
{
    protected $code = 500;

    protected $message = 'Failed to create process.';
}
