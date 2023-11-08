<?php

namespace BO\Zmsdb\Exception\Scope;

class MissingWorkstationCount extends \Exception
{
    protected $code = 404;

    protected $message = "Workstations count required to calculate waitingtime";
}
