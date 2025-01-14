<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Helper;

class ClientIpHelper
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
            'REMOTE_ADDR'
        ];
    
        foreach ($headers as $header) {
            if (isset($_SERVER[$header])) {
                // Handle comma-separated IPs (e.g. X-Forwarded-For)
                $ips = array_map('trim', explode(',', $_SERVER[$header]));
                $ip = $ips[0];
                
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
                return $ip;
            }
        }
        
        return '127.0.0.1';
    }
}
