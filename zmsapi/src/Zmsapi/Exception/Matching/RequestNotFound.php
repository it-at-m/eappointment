<?php

namespace BO\Zmsapi\Exception\Matching;

/**
 *
 */
class RequestNotFound extends \Exception
{
    protected int $code = 404;

    protected string $message = 'Request does not exists';
}
