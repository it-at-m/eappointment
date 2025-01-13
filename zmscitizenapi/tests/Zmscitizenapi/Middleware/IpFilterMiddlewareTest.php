<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Tests\Middleware;

use BO\Zmscitizenapi\Localization\ErrorMessages;
use BO\Zmscitizenapi\Middleware\IpFilterMiddleware;
use BO\Zmscitizenapi\Tests\MiddlewareTestCase;
use Psr\Http\Message\ServerRequestInterface;
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
        putenv('IP_BLACKLIST');
        \App::reinitializeMiddlewareConfig();
        $_SERVER = [];
    }

    protected function tearDown(): void
    {
        putenv('IP_BLACKLIST');
        \App::reinitializeMiddlewareConfig();
        $_SERVER = [];
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
        \App::reinitializeMiddlewareConfig();
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
        \App::reinitializeMiddlewareConfig();
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
        \App::reinitializeMiddlewareConfig();
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
        \App::reinitializeMiddlewareConfig();
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
        \App::reinitializeMiddlewareConfig();
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
        \App::reinitializeMiddlewareConfig();
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

    public function testParseIpListPerformance(): void
    {
        // Generate a large blacklist
        $ips = array_map(function($i) {
            return "192.168.{$i}.0/24";
        }, range(0, 255));
        
        putenv('IP_BLACKLIST=' . implode(',', $ips));
        \App::reinitializeMiddlewareConfig();
        
        $middleware = new IpFilterMiddleware($this->logger);
        $request = $this->createRequest(['REMOTE_ADDR' => '10.0.0.1']);
        $response = new Response();
        $handler = $this->createHandler($response);
    
        $startTime = microtime(true);
        $result = $middleware->process($request, $handler);
        $endTime = microtime(true);
    
        // Should process request in under 100ms even with large blacklist
        $this->assertLessThan(0.1, $endTime - $startTime);
        $this->assertSame($response, $result);
    }

}