<?php

namespace BO\Zmsdb\Exception\Useraccount;

class MissingAssignedDepartments extends \Exception
{
    protected int $code = 404;

    protected string $message = "Department not found or missing scopes for user assigned departments!";
}
