<?php

namespace BO\Zmsbackend\Useraccount\Exception;

/**
 * example class to generate an exception
 */
class AuthKeyFound extends \Exception
{
    protected $code = 200;

    protected $message = 'Your client is still logged in with an user account. Logout first.';

    public mixed $data = null;
}
