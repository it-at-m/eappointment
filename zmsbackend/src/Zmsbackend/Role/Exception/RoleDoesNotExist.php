<?php

namespace BO\Zmsbackend\Role\Exception;

class RoleDoesNotExist extends \Exception
{
    protected $code = 404;

    protected $message = 'Role id does not exist';
}
