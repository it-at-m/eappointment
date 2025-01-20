<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Tests\Middleware;

use BO\Zmscitizenapi\Localization\ErrorMessages;
use BO\Zmscitizenapi\Middleware\RateLimitingMiddleware;
use BO\Zmscitizenapi\Tests\MiddlewareTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\SimpleCache\CacheInterface;
use Slim\Psr7\Response;

class RateLimitingMiddlewareTest extends MiddlewareTestCase
{
    private RateLimitingMiddleware $middleware;
    private CacheInterface|MockObject $cache;

    protected function setUp(): void
    {
        parent::setUp();
        \App::$source_name = 'unittest';

        if (\App::$cache) {
            \App::$cache->clear();
        }
        $this->cache = $this->createMock(CacheInterface::class);
        $this->middleware = new RateLimitingMiddleware($this->cache, $this->logger);
    }

    protected function tearDown(): void
    {
        if (\App::$cache) {
            \App::$cache->clear();
        }
        parent::tearDown();
    }

    public function testAllowsRequestUnderLimit(): void
    {
        $request = $this->createRequest();
        $response = new Response();
        $handler = $this->createHandler($response);
        
        // Mock lock acquisition
        $this->cache->expects($this->once())
            ->method('has')
            ->willReturn(false);
            
        $this->cache->method('set')
            ->willReturnCallback(function(string $key, $value, $ttl) {
                if (str_contains($key, '_lock')) {
                    $this->assertTrue($value === true);
                    $this->assertEquals(1, $ttl);
                } else {
                    $this->assertIsArray($value);
                    $this->assertArrayHasKey('count', $value);
                    $this->assertEquals(2, $value['count']);
                    $this->assertEquals(60, $ttl);
                }
                return true;
            });

        $currentTime = time();
        $requestData = [
            'count' => 1,
            'timestamp' => $currentTime
        ];
        
        $this->cache->expects($this->any())
            ->method('get')
            ->willReturn($requestData);
            
        $this->cache->expects($this->any())
            ->method('delete')
            ->willReturn(true);

        $result = $this->middleware->process($request, $handler);

        $this->assertSame($response->getStatusCode(), $result->getStatusCode());
        $this->assertSame('58', $result->getHeaderLine('X-RateLimit-Remaining'));
        $this->assertSame('60', $result->getHeaderLine('X-RateLimit-Limit'));
        $this->assertNotEmpty($result->getHeaderLine('X-RateLimit-Reset'));
    }

    public function testBlocksRequestOverLimit(): void
    {
        $request = $this->createRequest();
        $response = new Response();
        $handler = $this->createHandler($response);

        // Mock lock acquisition
        $this->cache->expects($this->once())
            ->method('has')
            ->willReturn(false);

        $this->cache->method('set')
            ->willReturnCallback(function(string $key, $value, $ttl) {
                $this->assertTrue(str_contains($key, '_lock'));
                $this->assertTrue($value === true);
                $this->assertEquals(1, $ttl);
                return true;
            });

        $currentTime = time();
        $requestData = [
            'count' => 60,
            'timestamp' => $currentTime
        ];
        
        $this->cache->expects($this->any())
            ->method('get')
            ->willReturn($requestData);
            
        $this->cache->expects($this->any())
            ->method('delete')
            ->willReturn(true);

        $this->logger->expectLogInfo(sprintf(
            'Rate limit exceeded for IP %s. URI: %s',
            '127.0.0.1',
            'http://localhost/test'
        ));

        $result = $this->middleware->process($request, $handler);
        $logBody = json_decode((string) $result->getBody(), true);

        $this->assertEquals(ErrorMessages::get('rateLimitExceeded')['statusCode'], $result->getStatusCode());
        $this->assertEquals(
            ['errors' => [ErrorMessages::get('rateLimitExceeded')]],
            $logBody
        );
    }

    public function testHandlesFirstRequest(): void
    {
        $request = $this->createRequest();
        $response = new Response();
        $handler = $this->createHandler($response);

        // Mock lock acquisition
        $this->cache->expects($this->once())
            ->method('has')
            ->willReturn(false);

        $this->cache->method('set')
            ->willReturnCallback(function(string $key, $value, $ttl) {
                if (str_contains($key, '_lock')) {
                    $this->assertTrue($value === true);
                    $this->assertEquals(1, $ttl);
                } else {
                    $this->assertIsArray($value);
                    $this->assertArrayHasKey('count', $value);
                    $this->assertEquals(1, $value['count']);
                    $this->assertEquals(60, $ttl);
                }
                return true;
            });

        // For first request, always return null
        $this->cache->expects($this->any())
            ->method('get')
            ->willReturn(null);
            
        $this->cache->expects($this->any())
            ->method('delete')
            ->willReturn(true);

        $result = $this->middleware->process($request, $handler);

        $this->assertSame($response->getStatusCode(), $result->getStatusCode());
        $this->assertSame('59', $result->getHeaderLine('X-RateLimit-Remaining'));
        $this->assertSame('60', $result->getHeaderLine('X-RateLimit-Limit'));
        $this->assertNotEmpty($result->getHeaderLine('X-RateLimit-Reset'));
    }

    public function testHandlesCorruptedCacheData(): void
    {
        $request = $this->createRequest();
        $response = new Response();
        $handler = $this->createHandler($response);

        // Mock lock acquisition
        $this->cache->expects($this->once())
            ->method('has')
            ->willReturn(false);

        $this->cache->method('set')
            ->willReturnCallback(function(string $key, $value, $ttl) {
                $this->assertTrue(str_contains($key, '_lock'));
                $this->assertTrue($value === true);
                $this->assertEquals(1, $ttl);
                return true;
            });

        // Return corrupted data for rate limit key
        $this->cache->expects($this->any())
            ->method('get')
            ->willReturn('corrupted');

        // Handle both lock release and corrupted data cleanup
        $this->cache->expects($this->any())
            ->method('delete')
            ->willReturn(true);

        $result = $this->middleware->process($request, $handler);

        $this->assertSame($response->getStatusCode(), $result->getStatusCode());
        $this->assertSame('59', $result->getHeaderLine('X-RateLimit-Remaining'));
        $this->assertSame('60', $result->getHeaderLine('X-RateLimit-Limit'));
    }
    
    public function testBackoffExponentialGrowth(): void
    {
        $request = $this->createRequest();
        $response = new Response();
        $handler = $this->createHandler($response);
    
        // Lock held for first two attempts
        $this->cache->expects($this->exactly(3))
            ->method('has')
            ->willReturnOnConsecutiveCalls(true, true, false);
    
        $startTime = microtime(true);
        $result = $this->middleware->process($request, $handler);
        $endTime = microtime(true);
    
        // Second retry should have waited at least 4x backoffMin
        $minExpectedDelay = \App::getRateLimit()['backoffMin'] * 4 / 1000;
        $this->assertGreaterThan($minExpectedDelay, $endTime - $startTime);
        $this->assertSame($response->getStatusCode(), $result->getStatusCode());
    }
    
    public function testLockTimeout(): void
    {
        $request = $this->createRequest();
        $response = new Response();
        $handler = $this->createHandler($response);
    
        // Lock exists but times out
        $this->cache->method('has')
            ->willReturnCallback(function() {
                static $calls = 0;
                // Simulate lock timeout after lockTimeout seconds
                sleep(\App::getRateLimit()['lockTimeout']);
                return ++$calls === 1;
            });
    
        $result = $this->middleware->process($request, $handler);
        $this->assertSame($response->getStatusCode(), $result->getStatusCode());
    }
}