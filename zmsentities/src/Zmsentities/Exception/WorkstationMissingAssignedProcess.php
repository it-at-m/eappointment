<?php

namespace BO\Zmsentities\Exception;

class WorkstationMissingAssignedProcess extends \Exception
{
    protected $code = 404;

    protected $message = 'No process has been assigned to the selected workstation.';
}
