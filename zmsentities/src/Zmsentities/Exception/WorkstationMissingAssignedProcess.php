<?php

namespace BO\Zmsentities\Exception;

class WorkstationMissingAssignedProcess extends \Exception
{
    protected $code = 404;

    protected $message = 'workstation has not an assigned process, maybe it has been deleted';
}
