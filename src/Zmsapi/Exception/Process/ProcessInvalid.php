<?php

namespace BO\Zmsapi\Exception\Process;

/**
 * example class to generate an exception
 */
class ProcessInvalid extends \Exception
{
    protected $code = 400;

    protected $message = 'The input process is invalid';
}
