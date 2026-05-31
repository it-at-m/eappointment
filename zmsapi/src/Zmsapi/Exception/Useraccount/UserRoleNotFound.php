<?php

namespace BO\Zmsapi\Exception\Useraccount;

/**
 * example class to generate an exception
 */
class UserRoleNotFound extends \Exception
{
    protected int $code = 404;

    protected string $message = 'Ein Benutzer mit dieser Rolle wurde noch nicht erstellt.';
}
