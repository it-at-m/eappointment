<?php

namespace BO\Zmsapi\Exception\Useraccount;

class UseraccountAlreadyExists extends \Exception
{
    protected int $code = 404;

    protected string $message = 'useraccount already exists, please try other login data to add a new user';
}
