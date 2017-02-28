<?php

namespace BO\Zmsapi\Exception\Useraccount;

/**
 * example class to generate an exception
 */
class UseraccountNotFound extends \Exception
{
    protected $code = 404;

    protected $message = 'useraccount not found';
}
