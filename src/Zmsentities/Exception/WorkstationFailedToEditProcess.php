<?php

namespace BO\Zmsentities\Exception;

/**
 * example class to generate an exception
 */
class WorkstationFailedToEditProcess extends \Exception
{
    protected $error = 500;

    protected $message = "Workstation is not allowed to edit the process,
        process scope does not match with workstation cluster/scope";
}
