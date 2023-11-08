<?php

namespace BO\Zmsapi\Exception\Useraccount;

/**
 * example class to generate an exception
 */
class UserAlreadyLoggedIn extends \Exception
{
    protected $code = 404;

    protected $message = 'useraccount was already loggedin and is replaced by new login';
}
