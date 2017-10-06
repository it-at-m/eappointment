<?php

namespace BO\Zmsapi\Exception\Useraccount;

/**
 * example class to generate an exception
 */
class UseraccountInvalidInput extends \Exception
{
    protected $code = 404;

    protected $message = 'input data is not valid';
}
