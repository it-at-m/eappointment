<?php

namespace BO\Zmsentities\Exception;

class WorkstationMissingAssignedDepartments extends \Exception
{
    protected $code = 500;

    protected $message = "Department not found or missing scopes for user assigned departments!";
}
