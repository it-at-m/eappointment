<?php

namespace BO\Zmsdb\Exception\Scope;

class GivenNumberCountExceeded extends \Exception
{
    protected int $code = 404;

    protected string $message = "Queue numbers contingent exceeded";
}
