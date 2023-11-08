<?php

namespace BO\Zmsapi\Exception\Apikey;

class ApiKeyNotFound extends \Exception
{
    protected $code = 404;

    protected $message = 'Apikey not found';
}
