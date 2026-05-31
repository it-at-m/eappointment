<?php

namespace BO\Zmsapi\Exception\Workstation;

/**
 * example class to generate an exception
 */
class WorkstationNotFound extends \Exception
{
    protected int $code = 404;

    protected string $message = 'workstation is not logged in anymore';
}
