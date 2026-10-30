<?php

declare(strict_types=1);

namespace BO\Slim\Helper;

class ClientIp
{
    public static function getClientIp(): string
    {
        if (isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR'])) {
            $remoteAddr = trim($_SERVER['REMOTE_ADDR']);
            if (filter_var($remoteAddr, FILTER_VALIDATE_IP)) {
                return $remoteAddr;
            }
        }

        return '127.0.0.1';
    }
}
