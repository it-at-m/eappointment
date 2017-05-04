<?php

namespace BO\Zmsapi\Exception\Matching;

/**
 *
 */
class RequestNotFound extends \Exception
{
    protected $code = 404;

    protected $message = 'Request does not exists';
}
