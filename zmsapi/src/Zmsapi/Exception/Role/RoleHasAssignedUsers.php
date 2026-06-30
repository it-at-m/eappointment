<?php

namespace BO\Zmsapi\Exception\Role;

class RoleHasAssignedUsers extends \Exception
{
    protected $code = 409;

    protected $message = 'Role has assigned users. Remove all assignments before deleting.';
}