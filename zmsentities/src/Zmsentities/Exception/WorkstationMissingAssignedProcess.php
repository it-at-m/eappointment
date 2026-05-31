<?php

namespace BO\Zmsentities\Exception;

class WorkstationMissingAssignedProcess extends \Exception
{
    protected int $code = 404;

    protected string $message = 'workstation has not an assigned process, maybe it has been deleted';
}
