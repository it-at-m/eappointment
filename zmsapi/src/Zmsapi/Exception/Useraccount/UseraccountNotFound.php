<?php

namespace BO\Zmsapi\Exception\Useraccount;

/**
 * example class to generate an exception
 */
class UseraccountNotFound extends \Exception
{
    protected int $code = 404;

    protected string $message = 'useraccount not found';
}
