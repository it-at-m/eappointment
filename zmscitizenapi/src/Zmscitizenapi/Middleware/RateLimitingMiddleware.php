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
    private const MAX_RETRIES = 3;
    private const BACKOFF_MIN = 10; // milliseconds
    private const BACKOFF_MAX = 50; // milliseconds
    private const LOCK_TIMEOUT = 1; // seconds
    
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
            $lockKey = $key . '_lock';
            
            // Try to acquire rate limit with retries and exponential backoff
            $attempt = 0;
            $limited = false;
            
            while ($attempt < self::MAX_RETRIES) {
                try {
                    if ($this->acquireLock($lockKey)) {
                        try {
                            $limited = $this->checkAndIncrementLimit($key);
                            break;
                        } finally {
                            $this->releaseLock($lockKey);
                        }
                    }
                } catch (\Throwable $e) {
                    $this->logger->logError($e, $request);
                }
                
                $attempt++;
                if ($attempt < self::MAX_RETRIES) {
                    // Exponential backoff with jitter
                    $backoffMs = min(
                        self::BACKOFF_MAX,
                        (int)(self::BACKOFF_MIN * min(pow(2, $attempt), PHP_INT_MAX / self::BACKOFF_MIN))
                    );
                    $jitterMs = random_int(0, (int)($backoffMs * 0.1));
                    $sleepMs = $backoffMs + $jitterMs;
                    usleep($sleepMs * 1000); // Convert to microseconds
                }
            }
            
            if ($limited) {
                $this->logger->logInfo(sprintf(
                    'Rate limit exceeded for IP %s. URI: %s',
                    $ip,
                    $request->getUri()
                ));
                
                return $this->createRateLimitResponse();
            }
            
            $response = $handler->handle($request);
            // Subtract one extra to account for the current request
            $remaining = max(0, self::MAX_REQUESTS - $this->getCurrentRequestCount($key) - 1);
            
            return $response
                ->withHeader('X-RateLimit-Limit', (string)self::MAX_REQUESTS)
                ->withHeader('X-RateLimit-Remaining', (string)max(0, $remaining))
                ->withHeader('X-RateLimit-Reset', (string)$this->getResetTime($key));
                
        } catch (\Throwable $e) {
            $this->logger->logError($e, $request);
            throw $e;
        }
    }
    
    private function checkAndIncrementLimit(string $key): bool
    {
        $requestData = $this->cache->get($key);
        
        if ($requestData === null) {
            // First request
            $this->cache->set($key, [
                'count' => 1,
                'timestamp' => time()
            ], self::TIME_WINDOW);
            return false;
        }
        
        if (!is_array($requestData)) {
            // Handle corrupted data
            $this->cache->delete($key);
            return false;
        }
        
        $count = (int)($requestData['count'] ?? 0);
        
        if ($count >= self::MAX_REQUESTS) {
            return true;
        }
        
        // Update the counter atomically
        $requestData['count'] = $count + 1;
        $requestData['timestamp'] = time();
        $this->cache->set($key, $requestData, self::TIME_WINDOW);
        
        return false;
    }
    
    private function getCurrentRequestCount(string $key): int
    {
        $requestData = $this->cache->get($key);
        if (!is_array($requestData)) {
            return 0;
        }
        return (int)($requestData['count'] ?? 0);
    }
    
    private function getResetTime(string $key): int
    {
        $requestData = $this->cache->get($key);
        if (!is_array($requestData)) {
            return time() + self::TIME_WINDOW;
        }
        return (int)($requestData['timestamp'] ?? time()) + self::TIME_WINDOW;
    }
    
    private function createRateLimitResponse(): ResponseInterface
    {
        $response = \App::$slim->getResponseFactory()->createResponse();
        $response = $response->withStatus(ErrorMessages::get(self::ERROR_RATE_LIMIT)['statusCode'])
            ->withHeader('Content-Type', 'application/json');
        
        $response->getBody()->write(json_encode([
            'errors' => [ErrorMessages::get(self::ERROR_RATE_LIMIT)]
        ]));
        
        return $response;
    }
    
    private function acquireLock(string $lockKey): bool
    {
        // Try to acquire lock by setting it only if it doesn't exist
        if (!$this->cache->has($lockKey)) {
            return $this->cache->set($lockKey, true, self::LOCK_TIMEOUT);
        }
        return false;
    }
    
    private function releaseLock(string $lockKey): void
    {
        $this->cache->delete($lockKey);
    }
    
    /**
     * For testing purposes - allows checking if a lock exists
     */
    public function isLocked(string $ip): bool
    {
        $lockKey = 'rate_limit_' . md5($ip) . '_lock';
        return $this->cache->has($lockKey);
    }
}