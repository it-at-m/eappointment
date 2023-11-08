<?php

namespace BO\Zmsdb\Exception\Useraccount;

class DuplicateEntry extends \Exception
{
    protected $code = 500;

    protected $message = "The login (username) already exists";
}
