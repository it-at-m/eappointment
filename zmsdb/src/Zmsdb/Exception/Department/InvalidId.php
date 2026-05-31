<?php

namespace BO\Zmsdb\Exception\Department;

/**
 * class to generate an exception if children exists
 */
class InvalidId extends \Exception
{
    protected int $code = 500;

    protected string $message = 'The given department ID is invalid, processing canceled';
}
