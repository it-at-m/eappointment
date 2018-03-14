<?php

namespace BO\Zmsdb\Exception\Process;

class ProcessTimeout extends \Exception
{
    protected $code = 404;

    protected $message = 'Failed to lock database for updating process';
}
