<?php

namespace BO\Zmsadmin\Exception;

class NotAllowed extends \Exception
{
    protected int $code = 403;

    protected string $message = 'you are not allowed to access this service';
}
