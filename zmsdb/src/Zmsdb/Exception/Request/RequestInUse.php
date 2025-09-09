<?php

namespace BO\Zmsdb\Exception\Request;

class RequestInUse extends \Exception
{
    protected $code = 409;

    protected $message = 'request cannot be deleted because it is already in use';
}
