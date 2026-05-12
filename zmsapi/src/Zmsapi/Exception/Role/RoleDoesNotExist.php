<?php

namespace BO\Zmsapi\Exception\Role;

class RoleDoesNotExist extends \Exception
{
    protected $code = 404;

    protected $message = 'Role id does not exist';
}
