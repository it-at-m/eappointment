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

    public function testAllowsRequestUnderLimit(): void
    {
        $request = $this->createRequest();
        $response = new Response();
        $handler = $this->createHandler($response);
    
        $this->cache->expects($this->any())
            ->method('get')
            ->willReturn(1);
            
        $this->cache->expects($this->once())
            ->method('set')
            ->with(
                $this->anything(),
                $this->equalTo(2),
                $this->equalTo(60)
            );
    
        $result = $this->middleware->process($request, $handler);
        
        $this->assertEquals($response->getStatusCode(), $result->getStatusCode());  // Changed from assertSame
        $this->assertNotEmpty($result->getHeaderLine('X-RateLimit-Remaining'));
    }
    
    public function testBlocksRequestOverLimit(): void
    {
        $request = $this->createRequest();
        $response = new Response();
        $handler = $this->createHandler($response);
    
        $this->cache->expects($this->any())
            ->method('get')
            ->willReturn(60);
    
        $this->logger->expectLogInfo(sprintf(
            'Rate limit exceeded for IP %s. URI: %s',
            '127.0.0.1',
            'http://localhost/test'
        ));
    
        $result = $this->middleware->process($request, $handler);
        $logBody = json_decode((string)$result->getBody(), true);
    
        $this->assertEquals(ErrorMessages::get('rateLimitExceeded')['statusCode'], $result->getStatusCode());
        $this->assertEquals(
            ['errors' => [ErrorMessages::get('rateLimitExceeded')]],
            $logBody
        );
    }
}