<?php

namespace BO\Zmsentities\Exception;

class WorkstationMissingAssignedProcess extends \Exception
{
    protected $code = 404;

    protected $message = 'workstation has no assigned process, maybe it has been deleted';
}
