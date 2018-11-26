<?php

namespace BO\Zmsadmin\Exception;

class NotAllowed extends \Exception
{
    protected $code = 403;

    protected $message = 'you are not allowed to access this service';
}
