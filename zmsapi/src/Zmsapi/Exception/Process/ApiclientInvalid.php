<?php

namespace BO\Zmsapi\Exception\Process;

/**
 * example class to generate an exception
 */
class ApiclientInvalid extends \Exception
{
    protected $code = 403;

    protected $message = 'Invalid ApiClientKey';
}
