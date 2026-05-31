<?php

namespace BO\Zmsapi\Exception\Provider;

class RequestsMissed extends \Exception
{
    protected int $code = 400;

    protected string $message = 'Es wurden keine Dienstleistungen angegeben.';
}
