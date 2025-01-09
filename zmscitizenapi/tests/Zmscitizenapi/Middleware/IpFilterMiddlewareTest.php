<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Tests\Middleware;

use BO\Zmscitizenapi\Localization\ErrorMessages;
use BO\Zmscitizenapi\Middleware\IpFilterMiddleware;
use BO\Zmscitizenapi\Tests\MiddlewareTestCase;
use Slim\Psr7\Response;

class IpFilterMiddlewareTest extends MiddlewareTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        \App::$source_name = 'unittest';
        if (\App::$cache) {
            \App::$cache->clear();
        }
        putenv('IP_BLACKLIST'); // Clear any existing blacklist
        $_SERVER = []; // Reset server variables
    }

    protected function tearDown(): void
    {
        putenv('IP_BLACKLIST');
        $_SERVER = []; // Reset server variables
        parent::tearDown();
    }

    public function testAllowsRequestWithEmptyBlacklist(): void
    {
        $_SERVER['REMOTE_ADDR'] = '192.168.1.1';
        $middleware = new IpFilterMiddleware($this->logger);
        
        $request = $this->createRequest(['REMOTE_ADDR' => '192.168.1.1']);
        $response = new Response();
        $handler = $this->createHandler($response);

        /*$this->logger->expectLogInfo('Request processed successfully', [
            'uri' => 'http://localhost/test'
        ]);*/

        $result = $middleware->process($request, $handler);
        $this->assertSame($response, $result);
    }

    public function testAllowsNonBlacklistedIPv4(): void
    {
        putenv('IP_BLACKLIST=192.168.1.1,10.0.0.0/8');
        $_SERVER['REMOTE_ADDR'] = '192.168.2.1';
        $middleware = new IpFilterMiddleware($this->logger);
        
        $request = $this->createRequest(['REMOTE_ADDR' => '192.168.2.1']);
        $response = new Response();
        $handler = $this->createHandler($response);

        /*$this->logger->expectLogInfo('Request processed successfully', [
            'uri' => 'http://localhost/test'
        ]);*/

        $result = $middleware->process($request, $handler);
        $this->assertSame($response, $result);
    }

    public function testAllowsNonBlacklistedIPv6(): void
    {
        putenv('IP_BLACKLIST=2001:db8::/32,::1');
        $_SERVER['REMOTE_ADDR'] = '2001:db9::1';
        $middleware = new IpFilterMiddleware($this->logger);
        
        $request = $this->createRequest(['REMOTE_ADDR' => '2001:db9::1']);
        $response = new Response();
        $handler = $this->createHandler($response);

        /*$this->logger->expectLogInfo('Request processed successfully', [
            'uri' => 'http://localhost/test'
        ]);*/

        $result = $middleware->process($request, $handler);
        $this->assertSame($response, $result);
    }

    public function testBlocksBlacklistedIPv4(): void
    {
        putenv('IP_BLACKLIST=192.168.1.0/24');
        $_SERVER['REMOTE_ADDR'] = '192.168.1.1';
        $middleware = new IpFilterMiddleware($this->logger);
        
        $request = $this->createRequest(['REMOTE_ADDR' => '192.168.1.1']);
        $response = new Response();
        $handler = $this->createHandler($response);

        $this->logger->expectLogInfo('Access denied - IP blacklisted', [
            'ip' => '192.168.1.1',
            'uri' => 'http://localhost/test'
        ]);

        $result = $middleware->process($request, $handler);
        $responseData = json_decode((string)$result->getBody(), true);

        $this->assertEquals(ErrorMessages::get('ipBlacklisted')['statusCode'], $result->getStatusCode());
        $this->assertEquals(
            ['errors' => [ErrorMessages::get('ipBlacklisted')]],
            $responseData
        );
    }

    public function testBlocksBlacklistedIPv6(): void
    {
        putenv('IP_BLACKLIST=2001:db8::/32');
        $_SERVER['REMOTE_ADDR'] = '2001:db8:1::1';
        $middleware = new IpFilterMiddleware($this->logger);
        
        $request = $this->createRequest(['REMOTE_ADDR' => '2001:db8:1::1']);
        $response = new Response();
        $handler = $this->createHandler($response);

        $this->logger->expectLogInfo('Access denied - IP blacklisted', [
            'ip' => '2001:db8:1::1',
            'uri' => 'http://localhost/test'
        ]);

        $result = $middleware->process($request, $handler);
        $responseData = json_decode((string)$result->getBody(), true);

        $this->assertEquals(ErrorMessages::get('ipBlacklisted')['statusCode'], $result->getStatusCode());
        $this->assertEquals(
            ['errors' => [ErrorMessages::get('ipBlacklisted')]],
            $responseData
        );
    }

    public function testHandlesInvalidIP(): void
    {
        putenv('IP_BLACKLIST=192.168.1.1');
        $_SERVER['REMOTE_ADDR'] = 'invalid-ip';
        $middleware = new IpFilterMiddleware($this->logger);
        
        $request = $this->createRequest(['REMOTE_ADDR' => 'invalid-ip']);
        $response = new Response();
        $handler = $this->createHandler($response);

        $this->logger->expectLogInfo('Invalid IP address detected', [
            'ip' => 'invalid-ip',
            'uri' => 'http://localhost/test'
        ]);

        $result = $middleware->process($request, $handler);
        $this->assertSame($response, $result);
    }

    public function testHandlesInvalidBlacklistEntry(): void
    {
        putenv('IP_BLACKLIST=invalid-ip,192.168.1.1');
        $_SERVER['REMOTE_ADDR'] = '192.168.1.2';
        $middleware = new IpFilterMiddleware($this->logger);
        
        $request = $this->createRequest(['REMOTE_ADDR' => '192.168.1.2']);
        $response = new Response();
        $handler = $this->createHandler($response);

        /*$this->logger->expectLogInfo('Request processed successfully', [
            'uri' => 'http://localhost/test'
        ]);*/

        $result = $middleware->process($request, $handler);
        $this->assertSame($response, $result);
    }
}