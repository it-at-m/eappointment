<?php

namespace BO\Zmsapi\Exception\Workstation;

class WorkstationHasCalledProcess extends \Exception
{
    protected int $code = 428;

    protected string $message = 'A process is still called. It is required to finish the process before logout';
}
