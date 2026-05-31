<?php

namespace BO\Zmsdb\Exception\Request;

class RequestNotFound extends \Exception
{
    protected int $code = 404;

    protected string $message = "No request found for given ID";
}
