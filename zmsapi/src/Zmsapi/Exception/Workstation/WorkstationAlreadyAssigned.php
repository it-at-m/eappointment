<?php

namespace BO\Zmsapi\Exception\Workstation;

/**
 * example class to generate an exception
 */
class WorkstationAlreadyAssigned extends \Exception
{
    protected int $code = 200;

    protected string $message = 'workstation is already used by another user';
}
