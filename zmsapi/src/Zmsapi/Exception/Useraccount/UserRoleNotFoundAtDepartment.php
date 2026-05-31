<?php

namespace BO\Zmsapi\Exception\Useraccount;

/**
 * example class to generate an exception
 */
class UserRoleNotFoundAtDepartment extends \Exception
{
    protected int $code = 404;

    protected string $message = 'Ein Benutzer mit dieser Rolle wurde für Ihre Behörde(n) noch nicht erstellt.';
}
