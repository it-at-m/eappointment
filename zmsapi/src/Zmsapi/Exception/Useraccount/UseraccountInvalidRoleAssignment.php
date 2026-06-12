<?php

namespace BO\Zmsapi\Exception\Useraccount;

class UseraccountInvalidRoleAssignment extends \Exception
{
    protected $code = 400;

    protected $message = 'invalid role assignment';
}