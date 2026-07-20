<?php

namespace BO\Zmsbackend\Useraccount\Exception;

class UseraccountAlreadyExists extends \Exception
{
    protected $code = 404;

    protected $message = 'useraccount already exists, please try other login data to add a new user';
}
