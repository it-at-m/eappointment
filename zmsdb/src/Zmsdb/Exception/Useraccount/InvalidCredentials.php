<?php

namespace BO\Zmsdb\Exception\Useraccount;

class InvalidCredentials extends \Exception
{
    protected int $code = 401;

    protected string $message = "The login credentials (username, password) are invalid.";
}
