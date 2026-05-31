<?php

namespace BO\Zmsdb\Exception\Process;

class AvailabilityCreateFailed extends \Exception
{
    protected int $code = 500;

    protected string $message = 'Failed to create availability.';
}
