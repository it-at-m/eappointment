<?php

namespace BO\Zmsapi\Exception\Useraccount;

class UseraccountAlreadyExists extends \Exception
{
    protected $code = 404;

    protected $message = 'useraccount already exists, please try other login data to add a new user';
}
