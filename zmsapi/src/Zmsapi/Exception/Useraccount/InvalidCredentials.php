<?php

namespace BO\Zmsapi\Exception\Useraccount;

class InvalidCredentials extends \Exception
{
    protected $code = 401;

    protected $message = 'account credentials are invalid';

    public $data = [];
}
