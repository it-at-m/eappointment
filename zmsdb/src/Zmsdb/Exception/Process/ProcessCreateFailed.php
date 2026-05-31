<?php

namespace BO\Zmsdb\Exception\Process;

class ProcessCreateFailed extends \Exception
{
    protected int $code = 500;

    protected string $message = 'Failed to create process.';
}
