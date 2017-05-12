<?php

namespace BO\Zmsdb\Exception\Scope;

class GivenNumberCountExceeded extends \Exception
{
    protected $code = 404;

    protected $message = "Queue numbers contingent exceeded";
}
