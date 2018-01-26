<?php

namespace BO\Zmsapi\Exception\Useraccount;

/**
 * example class to generate an exception
 */
class UseraccountNoDepartments extends \Exception
{
    protected $code = 404;

    protected $message = 'Departments required but found empty list';
}
