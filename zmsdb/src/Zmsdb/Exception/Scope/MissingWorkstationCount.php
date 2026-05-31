<?php

namespace BO\Zmsdb\Exception\Scope;

class MissingWorkstationCount extends \Exception
{
    protected int $code = 404;

    protected string $message = "Workstations count required to calculate waitingtime";
}
