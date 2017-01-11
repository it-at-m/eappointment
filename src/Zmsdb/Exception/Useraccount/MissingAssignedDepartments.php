<?php

namespace BO\Zmsdb\Exception\Useraccount;

class MissingAssignedDepartments extends \Exception
{
    protected $error = 500;

    protected $message = "Department not found or missing scopes for user assigned departments!";
}
