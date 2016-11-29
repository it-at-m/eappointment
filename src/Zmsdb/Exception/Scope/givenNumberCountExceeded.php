<?php

namespace BO\Zmsdb\Exception\Scope;

class GivenNumberCountExceeded extends \Exception
{
    protected $error = 500;

    protected $message = "Queue numbers contingent exceeded";
}
