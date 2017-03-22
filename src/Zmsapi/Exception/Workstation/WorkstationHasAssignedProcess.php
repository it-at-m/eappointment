<?php

namespace BO\Zmsapi\Exception\Workstation;

/**
 * example class to generate an exception
 */
class WorkstationHasAssignedProcess extends \Exception
{
    protected $code = 404;

    protected $message = 'workstation has already an assigned process';
}
