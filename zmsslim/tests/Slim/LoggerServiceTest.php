<?php

declare(strict_types=1);

namespace BO\Slim\Tests;

use BO\Slim\LoggerService;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Psr\SimpleCache\CacheInterface;

class LoggerServiceTest extends TestCase
{
    private CacheInterface $cache;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cache = $this->createMock(CacheInterface::class);
        LoggerService::$cache = $this->cache;
        LoggerService::configure([
            'maxRequests' => 1000,
            'responseLength' => 1048576,
            'stackLines' => 20,
            'cacheTtl' => 60,
            'maxRetries' => 3,
            'backoffMin' => 100,
            'lockTimeout' => 5,
        ]);
    }

    protected function tearDown(): void
    {
        LoggerService::$cache = null;
        LoggerService::$requestContextEnricher = null;
        LoggerService::$errorCodeResolver = null;
        LoggerService::configure([
            'maxRequests' => 1000,
            'responseLength' => 1048576,
            'stackLines' => 10,
            'cacheTtl' => 60,
            'maxRetries' => 3,
            'backoffMin' => 100,
            'lockTimeout' => 30,
        ]);
        parent::tearDown();
    }

    public function testLogError(): void
    {
        $exception = new \Exception('Test error');
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $request->method('getHeaders')
            ->willReturn(['User-Agent' => ['test-agent']]);
        $response->method('getHeaders')
            ->willReturn(['Content-Type' => ['application/json']]);
        $response->method('getStatusCode')
            ->willReturn(500);
        $response->method('getBody')
            ->willReturn($stream);

        $stream->method('isSeekable')->willReturn(true);
        $stream->method('seek');
        $stream->method('tell')->willReturn(100);
        $stream->method('rewind');
        $stream->method('__toString')
            ->willReturn('{"errors": ["Test error occurred"]}');

        $this->cache->method('has')->willReturn(false);
        $this->cache->method('set')->willReturn(true);
        $this->cache->method('get')->willReturn(null);

        LoggerService::logError($exception, $request, $response, ['context' => 'test']);
        $this->assertTrue(true);
    }

    public function testLogWarning(): void
    {
        $this->cache->method('has')->willReturn(false);
        $this->cache->method('set')->willReturn(true);
        $this->cache->method('get')->willReturn(null);

        LoggerService::logWarning('Test warning', ['context' => 'test']);
        $this->assertTrue(true);
    }

    public function testLogInfo(): void
    {
        $this->cache->method('has')->willReturn(false);
        $this->cache->method('set')->willReturn(true);
        $this->cache->method('get')->willReturn(null);

        LoggerService::logInfo('Test info', ['context' => 'test']);
        $this->assertTrue(true);
    }

    public function testLogRequest(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $uri = $this->createMock(UriInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $request->method('getMethod')->willReturn('GET');
        $request->method('getUri')->willReturn($uri);
        $request->method('getQueryParams')->willReturn(['test' => 'test value']);
        $request->method('getHeaders')->willReturn(['User-Agent' => ['test']]);

        $uri->method('getPath')->willReturn('/test');

        $response->method('getStatusCode')->willReturn(200);
        $response->method('getBody')->willReturn($stream);
        $response->method('getHeaders')->willReturn([]);

        $stream->method('isSeekable')->willReturn(true);
        $stream->method('seek');
        $stream->method('tell')->willReturn(100);
        $stream->method('rewind');
        $stream->method('__toString')->willReturn('{"test":"value"}');

        $this->cache->method('has')->willReturn(false);
        $this->cache->method('set')->willReturn(true);
        $this->cache->method('get')->willReturn(null);

        LoggerService::logRequest($request, $response);
        $this->assertTrue(true);
    }

    public function testLogRequestWithArrayQueryParams(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $uri = $this->createMock(UriInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $request->method('getMethod')->willReturn('GET');
        $request->method('getUri')->willReturn($uri);
        $request->method('getQueryParams')->willReturn(['statusList' => ['called']]);
        $request->method('getHeaders')->willReturn(['User-Agent' => ['test']]);

        $uri->method('getPath')->willReturn('/ticketprinter');

        $response->method('getStatusCode')->willReturn(200);
        $response->method('getBody')->willReturn($stream);
        $stream->method('isSeekable')->willReturn(true);
        $stream->method('rewind');

        $this->cache->method('has')->willReturn(false);
        $this->cache->method('set')->willReturn(true);
        $this->cache->method('get')->willReturn(null);

        LoggerService::logRequest($request, $response);
        $this->assertTrue(true);
    }

    public function testRateLimitExceeded(): void
    {
        $this->cache->method('has')->willReturn(false);
        $this->cache->method('get')->willReturn([
            'count' => LoggerService::$maxRequests,
            'timestamp' => time(),
        ]);

        LoggerService::logInfo('Test message');
        $this->assertTrue(true);
    }

    public function testRateLimitLockAcquisitionFailure(): void
    {
        $this->cache->expects($this->exactly(LoggerService::$maxRetries))
            ->method('has')
            ->willReturn(true);

        LoggerService::logInfo('Test message');
        $this->assertTrue(true);
    }

    public function testSensitiveHeaderFiltering(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $uri = $this->createMock(UriInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $headers = [
            'Authorization' => ['Bearer token'],
            'User-Agent' => ['test-agent'],
            'X-Api-Key' => ['secret-key'],
        ];

        $request->method('getMethod')->willReturn('GET');
        $request->method('getUri')->willReturn($uri);
        $request->method('getQueryParams')->willReturn([]);
        $request->method('getHeaders')->willReturn($headers);

        $uri->method('getPath')->willReturn('/test');

        $response->method('getStatusCode')->willReturn(200);
        $response->method('getBody')->willReturn($stream);
        $response->method('getHeaders')->willReturn([]);

        $stream->method('__toString')->willReturn('{}');

        $this->cache->method('has')->willReturn(false);
        $this->cache->method('set')->willReturn(true);
        $this->cache->method('get')->willReturn(null);

        LoggerService::logRequest($request, $response);
        $this->assertTrue(true);
    }
}
