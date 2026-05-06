<?php

namespace BO\Zmsapi\Exception\Role;

class RoleNotFound extends \Exception
{
    protected $code = 404;

    protected $message = 'Role id does not exist';
}
