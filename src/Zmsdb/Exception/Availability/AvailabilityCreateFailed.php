<?php

namespace BO\Zmsdb\Exception\Process;

class AvailabilityCreateFailed extends \Exception
{
    protected $code = 500;

    protected $message = 'Failed to create availability.';
}
