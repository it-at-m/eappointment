<?php

namespace BO\Zmsapi\Exception\Process;

class ProcessReservedNotCallable extends \Exception
{
    protected int $code = 404;

    protected string $message = 'Failed to call process. Status reserved does not allow calling this process';
}
