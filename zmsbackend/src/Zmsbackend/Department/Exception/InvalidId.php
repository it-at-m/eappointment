<?php

namespace BO\Zmsbackend\Department\Exception;

/**
 * class to generate an exception if children exists
 */
class InvalidId extends \Exception
{
    protected $code = 500;

    protected $message = 'The given department ID is invalid, processing canceled';
}
