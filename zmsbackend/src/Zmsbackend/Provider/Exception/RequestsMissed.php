<?php

namespace BO\Zmsbackend\Provider\Exception;

class RequestsMissed extends \Exception
{
    protected $code = 400;

    protected $message = 'Es wurden keine Dienstleistungen angegeben.';
}
