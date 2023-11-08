<?php

namespace BO\Zmsapi\Exception\Provider;

class RequestsMissed extends \Exception
{
    protected $code = 400;

    protected $message = 'Es wurden keine Dienstleistungen angegeben.';
}
