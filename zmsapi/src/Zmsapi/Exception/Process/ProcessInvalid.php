<?php

namespace BO\Zmsapi\Exception\Process;

/**
 * example class to generate an exception
 */
class ProcessInvalid extends \Exception
{
    protected int $code = 400;

    protected string $message = 'The input process is invalid';
}
