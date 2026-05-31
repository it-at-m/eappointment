<?php

namespace BO\Zmsapi\Exception\Process;

/**
 * example class to generate an exception
 */
class ApiclientInvalid extends \Exception
{
    protected int $code = 403;

    protected string $message = 'Invalid ApiClientKey';
}
