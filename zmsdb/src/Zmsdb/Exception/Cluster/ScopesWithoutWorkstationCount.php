<?php

namespace BO\Zmsdb\Exception\Cluster;

class ScopesWithoutWorkstationCount extends \Exception
{
    protected $code = 404;

    protected $message = "Workstations count required to calculate waitingtime. No scope with workstations found.";
}
