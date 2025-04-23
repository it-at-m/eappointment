<?php

namespace BO\Zmsapi\Exception\Useraccount;

/**
 * example class to generate an exception
 */
class UserRoleNotFound extends \Exception
{
    protected $code = 404;

    protected $message = 'Ein Benutzer mit dieser Rolle wurde noch nicht erstellt.';
}
