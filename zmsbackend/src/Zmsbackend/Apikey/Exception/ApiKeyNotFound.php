<?php

namespace BO\Zmsbackend\Apikey\Exception;

class ApiKeyNotFound extends \Exception
{
    protected $code = 404;

    protected $message = 'Apikey not found';
}
