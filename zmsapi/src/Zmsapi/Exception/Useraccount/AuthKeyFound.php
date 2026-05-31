<?php

namespace BO\Zmsapi\Exception\Useraccount;

/**
 * example class to generate an exception
 */
class AuthKeyFound extends \Exception
{
    protected int $code = 200;

    protected string $message = 'Your client is still logged in with an user account. Logout first.';

    public mixed $data = null;
}
