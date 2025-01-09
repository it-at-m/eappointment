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
use Psr\SimpleCache\CacheInterface;

class RateLimitingMiddleware implements MiddlewareInterface
{
    private const ERROR_RATE_LIMIT = 'rateLimitExceeded';
    private const MAX_REQUESTS = 60;
    private const TIME_WINDOW = 60;

    private CacheInterface $cache;
    private LoggerService $logger;

    public function __construct(CacheInterface $cache, LoggerService $logger)
    {
        $this->cache = $cache;
        $this->logger = $logger;
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        try {
            $ip = ClientIpHelper::getClientIp();
            $key = 'rate_limit_' . md5($ip);
            
            if ($this->isLimitExceeded($key)) {
                $this->logger->logInfo(sprintf(
                    'Rate limit exceeded for IP %s. URI: %s',
                    $ip,
                    $request->getUri()
                ));
                
                $response = \App::$slim->getResponseFactory()->createResponse();
                $response = $response->withStatus(ErrorMessages::get(self::ERROR_RATE_LIMIT)['statusCode'])
                    ->withHeader('Content-Type', 'application/json');
                
                $response->getBody()->write(json_encode([
                    'errors' => [ErrorMessages::get(self::ERROR_RATE_LIMIT)]
                ]));
                
                return $response;
            }

            $response = $handler->handle($request);
            $remaining = self::MAX_REQUESTS - $this->getCurrentRequestCount($key);
            
            return $response
                ->withHeader('X-RateLimit-Limit', (string)self::MAX_REQUESTS)
                ->withHeader('X-RateLimit-Remaining', (string)$remaining)
                ->withHeader('X-RateLimit-Reset', (string)$this->getResetTime($key));
        } catch (\Throwable $e) {
            $this->logger->logError($e, $request);
            throw $e;
        }
    }

    private function isLimitExceeded(string $key): bool
    {
        $count = $this->getCurrentRequestCount($key);
        if ($count >= self::MAX_REQUESTS) {
            return true;
        }
        
        $this->incrementRequestCount($key);
        return false;
    }

    private function getCurrentRequestCount(string $key): int
    {
        return (int)$this->cache->get($key, 0);
    }

    private function incrementRequestCount(string $key): void
    {
        $count = $this->getCurrentRequestCount($key);
        $this->cache->set($key, $count + 1, self::TIME_WINDOW);
    }

    private function getResetTime(string $key): int
    {
        return time() + self::TIME_WINDOW;
    }
}