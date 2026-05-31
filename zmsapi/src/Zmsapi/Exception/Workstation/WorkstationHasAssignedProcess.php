<?php

namespace BO\Zmsapi\Exception\Workstation;

class WorkstationHasAssignedProcess extends \Exception
{
    protected int $code = 404;

    protected string $message = 'workstation has already an assigned process';

    public mixed $data = null;
}
