<?php

namespace BO\Zmsentities\Exception;

/**
 * example class to generate an exception
 */
class WorkstationMissingAssignedDepartments extends \Exception
{
    protected $error = 500;

    protected $message = "Department not found or missing scopes for user assigned departments!";
}
