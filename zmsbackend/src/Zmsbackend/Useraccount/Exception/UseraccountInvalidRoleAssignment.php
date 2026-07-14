<?php

namespace BO\Zmsbackend\Useraccount\Exception;

class UseraccountInvalidRoleAssignment extends \Exception
{
    protected $code = 400;

    protected $message = 'invalid role assignment';
}
