<?php

namespace BO\Zmsbackend\Workstation\Exception;

class WorkstationHasAssignedProcess extends \Exception
{
    protected $code = 404;

    protected $message = 'workstation has already an assigned process';

    public mixed $data = null;
}
