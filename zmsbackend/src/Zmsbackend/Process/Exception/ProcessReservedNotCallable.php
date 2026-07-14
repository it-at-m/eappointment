<?php

namespace BO\Zmsbackend\Process\Exception;

class ProcessReservedNotCallable extends \Exception
{
    protected $code = 404;

    protected $message = 'Failed to call process. Status reserved does not allow calling this process';
}
