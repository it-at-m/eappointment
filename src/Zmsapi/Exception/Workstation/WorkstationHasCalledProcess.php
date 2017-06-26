<?php

namespace BO\Zmsapi\Exception\Workstation;

class WorkstationHasCalledProcess extends \Exception
{
    protected $code = 428;

    protected $message = 'A process is still called. It is required to finish the process before logout';
}
