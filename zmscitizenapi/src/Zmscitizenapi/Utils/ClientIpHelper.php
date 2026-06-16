<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Utils;

use BO\Slim\Helper\ClientIp;

class ClientIpHelper
{
    public static function getClientIp(): string
    {
        return ClientIp::getClientIp();
    }
}
