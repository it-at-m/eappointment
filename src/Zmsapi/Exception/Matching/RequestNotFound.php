<?php

namespace BO\Zmsapi\Exception\Matching;

/**
 *
 */
class RequestNotFound extends \Exception
{
    protected $code = 500;

    protected $message = 'Request does not exists';
}
