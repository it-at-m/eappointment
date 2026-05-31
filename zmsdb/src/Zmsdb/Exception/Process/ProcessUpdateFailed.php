<?php

namespace BO\Zmsdb\Exception\Process;

class ProcessUpdateFailed extends \Exception
{
    protected int $code = 500;

    protected string $message = 'Failed to update process.';
}
