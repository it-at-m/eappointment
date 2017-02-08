<?php

namespace BO\Zmsapi\Exception\Workstation;

/**
 * example class to generate an exception
 */
class WorkstationNotFound extends \Exception
{
    protected $code = 404;

    protected $message = 'workstation is not logged in anymore';
}
