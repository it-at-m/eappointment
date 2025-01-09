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

    private LoggerService $logger;
    private array $blacklist;

    /**
     * @param LoggerService $logger Service for logging access attempts
     */
    public function __construct(LoggerService $logger)
    {
        $this->logger = $logger;
        $this->blacklist = $this->parseIpList(getenv('IP_BLACKLIST') ?: null);
    }

    private function parseIpList(?string $ipList): array
    {
        if (empty($ipList)) {
            return [];
        }
        
        return array_map('trim', explode(',', $ipList));
    }
    
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        try {
            $ip = ClientIpHelper::getClientIp();
            
            if ($this->isBlacklisted($ip)) {
                $this->logger->logInfo(sprintf(
                    'Access denied - IP %s is blacklisted. URI: %s',
                    $ip,
                    $request->getUri()
                ));
                
                $response = \App::$slim->getResponseFactory()->createResponse();
                $response = $response->withStatus(ErrorMessages::get(self::ERROR_BLACKLISTED)['statusCode'])
                    ->withHeader('Content-Type', 'application/json');
                
                $response->getBody()->write(json_encode([
                    'errors' => [ErrorMessages::get(self::ERROR_BLACKLISTED)]
                ]));
                
                return $response;
            }
            
            return $handler->handle($request);
            
        } catch (\Throwable $e) {
            $this->logger->logError($e, $request);
            throw $e;
        }
    }
    
    private function isBlacklisted(string $ip): bool
    {
        return $this->isIpInList($ip, $this->blacklist);
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
        if (strpos($range, '/') !== false) {
            // CIDR notation
            list($subnet, $bits) = explode('/', $range);
            $ip = ip2long($ip);
            $subnet = ip2long($subnet);
            $mask = -1 << (32 - (int)$bits);
            $subnet &= $mask;
            return ($ip & $mask) === $subnet;
        }
        
        // Single IP
        return $ip === $range;
    }
}