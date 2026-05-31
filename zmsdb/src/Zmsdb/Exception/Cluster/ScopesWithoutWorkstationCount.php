<?php

namespace BO\Zmsdb\Exception\Cluster;

class ScopesWithoutWorkstationCount extends \Exception
{
    protected int $code = 404;

    protected string $message = "Workstations count required to calculate waitingtime. No scope with workstations found.";
}
