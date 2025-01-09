<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Helper;

class ClientIpHelper
{
    public static function getClientIp(): string
    {
        $headers = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        foreach ($headers as $header) {
            if (isset($_SERVER[$header])) {
                return $_SERVER[$header];
            }
        }
        return '127.0.0.1';
    }
}
