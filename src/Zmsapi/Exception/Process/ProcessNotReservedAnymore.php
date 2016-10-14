<?php

namespace BO\Zmsapi\Exception\Process;

/**
 * example class to generate an exception
 */
class ProcessNotReservedAnymore extends \Exception
{
    protected $code = 404;
    protected $message = 'Failed to confirm process. Maybe time of reservation went out.';
}
