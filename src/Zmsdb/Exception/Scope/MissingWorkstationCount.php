<?php

namespace BO\Zmsdb\Exception\Scope;

class MissingWorkstationCount extends \Exception
{
    protected $error = 500;

    protected $message = "Workstations count required to calculate waitingtime";
}
