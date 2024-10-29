<?php

namespace BO\Zmsapi\Exception\Useraccount;

/**
 * example class to generate an exception
 */
class UserRoleNotFoundAtDepartment extends \Exception
{
    protected $code = 404;

    protected $message = 'Ein Benutzer mit dieser Rolle wurde für Ihre Behörde(n) noch nicht erstellt.';
}
