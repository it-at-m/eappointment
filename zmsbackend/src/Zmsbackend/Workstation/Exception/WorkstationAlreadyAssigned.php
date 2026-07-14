<?php

namespace BO\Zmsbackend\Workstation\Exception;

/**
 * example class to generate an exception
 */
class WorkstationAlreadyAssigned extends \Exception
{
    protected $code = 200;

    protected $message = 'workstation is already used by another user';
}
