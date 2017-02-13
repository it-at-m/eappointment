<?php

namespace BO\Zmsapi\Exception\Useraccount;

/**
 * example class to generate an exception
 */
class InvalidCredentials extends \Exception
{
    protected $code = 401;

    protected $message = 'account credentials are invalid';
}
