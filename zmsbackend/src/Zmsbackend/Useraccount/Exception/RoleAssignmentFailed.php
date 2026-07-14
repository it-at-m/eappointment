<?php

namespace BO\Zmsbackend\Useraccount\Exception;

class RoleAssignmentFailed extends \Exception
{
    protected $code = 500;

    protected $message = 'Failed to assign exactly one role to the useraccount. Role assignment aborted.';
}
