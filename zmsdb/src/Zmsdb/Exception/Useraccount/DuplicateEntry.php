<?php

namespace BO\Zmsdb\Exception\Useraccount;

class DuplicateEntry extends \Exception
{
    protected int $code = 500;

    protected string $message = "The login (username) already exists";
}
