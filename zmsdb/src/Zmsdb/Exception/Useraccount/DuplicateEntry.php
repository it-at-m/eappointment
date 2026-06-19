<?php

namespace BO\Zmsbackend\Useraccount\Exception;

class DuplicateEntry extends \Exception
{
    protected $code = 500;

    protected $message = "The login (username) already exists";
}
