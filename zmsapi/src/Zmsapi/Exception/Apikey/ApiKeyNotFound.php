<?php

namespace BO\Zmsapi\Exception\Apikey;

class ApiKeyNotFound extends \Exception
{
    protected int $code = 404;

    protected string $message = 'Apikey not found';
}
