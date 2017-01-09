<?php

namespace BO\Zmsdb\Exception\Cluster;

class ScopesWithoutWorkstationCount extends \Exception
{
    protected $error = 500;

    protected $message = "Workstations count required to calculate waitingtime. No scope with workstations found.";
}
