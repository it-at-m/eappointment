<?php

namespace BO\Zmsapi\Exception\Process;

class ProcessReservedNotCallable extends \Exception
{
    protected $code = 404;

    protected $message = 'Failed to call process. Status reserved does not allow calling this process';
}
