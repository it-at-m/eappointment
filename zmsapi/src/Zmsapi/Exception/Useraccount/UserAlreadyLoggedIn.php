<?php

namespace BO\Zmsapi\Exception\Useraccount;

/**
 * example class to generate an exception
 */
class UserAlreadyLoggedIn extends \Exception
{
    protected int $code = 404;

    protected string $message = 'useraccount was already loggedin and is replaced by new login';
}
