<?php

namespace BO\Zmsapi\Exception\Workstation;

class WorkstationHasAssignedProcess extends \Exception
{
    protected $code = 404;

    protected $message = 'workstation has already an assigned process';
}
