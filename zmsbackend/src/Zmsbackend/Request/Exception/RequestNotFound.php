<?php

namespace BO\Zmsbackend\Request\Exception;

class RequestNotFound extends \Exception
{
    protected $code = 404;

    protected $message = "No request found for given ID";
}
