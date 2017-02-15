<?php

namespace BO\Zmsdb\Exception\Useraccount;

class InvalidCredentials extends \Exception
{
    protected $error = 401;

    protected $message = "The login credentials (username, password) are invalid.";
}
