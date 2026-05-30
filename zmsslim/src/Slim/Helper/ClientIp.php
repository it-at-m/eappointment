<?php

declare(strict_types=1);

namespace BO\Slim\Helper;

class ClientIp
{
    public static function getClientIp(): string
    {
        $headers = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR',
        ];

        foreach ($headers as $header) {
            if (!isset($_SERVER[$header])) {
                continue;
            }

            $ips = array_map('trim', explode(',', (string) $_SERVER[$header]));
            $ip = $ips[0];
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }

            return $ip;
        }

        return '127.0.0.1';
    }
}
