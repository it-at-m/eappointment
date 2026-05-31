<?php

namespace BO\Zmsentities\Exception;

class WorkstationMissingAssignedDepartments extends \Exception
{
    protected int $code = 500;

    protected string $message = "Department not found or missing scopes for user assigned departments!";
}
