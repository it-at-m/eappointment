<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Middleware;

use BO\Zmscitizenapi\Helper\ClientIpHelper;
use BO\Zmscitizenapi\Localization\ErrorMessages;
use BO\Zmscitizenapi\Services\Core\LoggerService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class IpFilterMiddleware implements MiddlewareInterface
{
    private const ERROR_BLACKLISTED = 'ipBlacklisted';
    private const IPV4_BITS = 32;
    private const IPV6_BITS = 128;
    
    private string $blacklist;
    private LoggerService $logger;

    public function __construct(LoggerService $logger)
    {
        $this->logger = $logger;
        $this->blacklist = \App::getIpBlacklist();
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        try {
            $ip = ClientIpHelper::getClientIp();
            $uri = (string)$request->getUri();
            if ($ip === null || !filter_var($ip, FILTER_VALIDATE_IP)) {
                $this->logger->logInfo('Invalid IP address detected', [
                    'ip' => $ip,
                    'uri' => $uri
                ]);
                return $handler->handle($request);
            }
            
            $blacklist = $this->parseIpList($this->blacklist ?: null);
            if ($this->isIpInList($ip, $blacklist)) {
                $this->logger->logInfo('Access denied - IP blacklisted', [
                    'ip' => $ip,
                    'uri' => $uri
                ]);
                
                $response = \App::$slim->getResponseFactory()->createResponse();
                $error = ErrorMessages::get(self::ERROR_BLACKLISTED);
                $response = $response->withStatus($error['statusCode'])
                    ->withHeader('Content-Type', 'application/json');
                
                $response->getBody()->write(json_encode([
                    'errors' => [$error]
                ]));
                
                return $response;
            }
            
            /*$this->logger->logInfo('Request processed successfully', [
                'uri' => $uri
            ]);*/
            return $handler->handle($request);
            
        } catch (\Throwable $e) {
            $this->logger->logError($e, $request);
            throw $e;
        }
    }
    
    private function parseIpList(?string $ipList): array
    {
        if (empty($ipList)) {
            return [];
        }
        
        $list = array_map('trim', explode(',', $ipList));
        return array_filter($list, function ($entry) {
            if (strpos($entry, '/') !== false) {
                list($ip, $bits) = explode('/', $entry);
                return filter_var($ip, FILTER_VALIDATE_IP) && 
                       is_numeric($bits) && 
                       (int)$bits >= 0 && 
                       (int)$bits <= (strpos($ip, ':') !== false ? self::IPV6_BITS : self::IPV4_BITS);
            }
            return filter_var($entry, FILTER_VALIDATE_IP);
        });
    }
    
    private function isIpInList(string $ip, array $list): bool
    {
        if (empty($list)) {
            return false;
        }
        
        foreach ($list as $range) {
            if ($this->isIpInRange($ip, $range)) {
                return true;
            }
        }
        
        return false;
    }
    
    private function isIpInRange(string $ip, string $range): bool
    {
        $flags = FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6;
        if (!filter_var($ip, FILTER_VALIDATE_IP, $flags)) {
            return false;
        }

        if (strpos($range, '/') !== false) {
            list($subnet, $bits) = explode('/', $range);
            
            if (!filter_var($subnet, FILTER_VALIDATE_IP, $flags)) {
                return false;
            }
            
            $ipBin = @inet_pton($ip);
            $subnetBin = @inet_pton($subnet);
            
            if ($ipBin === false || $subnetBin === false || 
                strlen($ipBin) !== strlen($subnetBin)) {
                return false;
            }
            
            $bits = (int)$bits;
            $maxBits = strlen($ipBin) === 4 ? self::IPV4_BITS : self::IPV6_BITS;
            
            if ($bits < 0 || $bits > $maxBits) {
                return false;
            }
            
            $bytes = strlen($ipBin);
            $mask = str_repeat("\xFF", (int)($bits / 8));
            
            if ($bits % 8) {
                $mask .= chr(0xFF << (8 - ($bits % 8)));
            }
            
            $mask = str_pad($mask, $bytes, "\x00");
            return ($ipBin & $mask) === ($subnetBin & $mask);
        }
        
        return $ip === $range;
    }
}