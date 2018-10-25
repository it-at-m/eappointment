<?php

namespace BO\Zmsdb\Exception\Request;

class RequestNotFound extends \Exception
{
    protected $code = 404;

    protected $message = "No request found for given ID";
}
