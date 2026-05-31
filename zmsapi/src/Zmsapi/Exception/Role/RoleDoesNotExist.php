<?php

namespace BO\Zmsapi\Exception\Role;

class RoleDoesNotExist extends \Exception
{
    protected int $code = 404;

    protected string $message = 'Role id does not exist';
}
