<?php

namespace BO\Zmsbackend\Process\Exception;

/**
 * example class to generate an exception
 */
class ApiclientInvalid extends \Exception
{
    protected $code = 403;

    protected $message = 'Invalid ApiClientKey';
}
