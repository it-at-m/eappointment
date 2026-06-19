<?php

namespace BO\Zmsbackend\Scope\Exception;

class GivenNumberCountExceeded extends \Exception
{
    protected $code = 404;

    protected $message = "Queue numbers contingent exceeded";
}
