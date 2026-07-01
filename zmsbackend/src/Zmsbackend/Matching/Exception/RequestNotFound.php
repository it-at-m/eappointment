<?php

namespace BO\Zmsbackend\Matching\Exception;

/**
 *
 */
class RequestNotFound extends \Exception
{
    protected $code = 404;

    protected $message = 'Request does not exists';
}
