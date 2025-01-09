<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Tests\Middleware;

use BO\Zmscitizenapi\Localization\ErrorMessages;
use BO\Zmscitizenapi\Middleware\CorsMiddleware;
use BO\Zmscitizenapi\Tests\MiddlewareTestCase;
use Slim\Psr7\Response;

class CorsMiddlewareTest extends MiddlewareTestCase
{
    private CorsMiddleware $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        \App::$source_name = 'unittest';

        if (\App::$cache) {
            \App::$cache->clear();
        }
        putenv('CORS=http://localhost:8080');
        $this->middleware = new CorsMiddleware($this->logger);
    }

    protected function tearDown(): void
    {
        putenv('CORS');  // Clear environment variable
        parent::tearDown();
    }

    public function testAllowsRequestWithoutOrigin(): void
    {
        $request = $this->createRequest();
        $response = new Response();
        $handler = $this->createHandler($response);

        $this->logger->expectLogInfo('Direct browser request - no Origin header', [
            'uri' => 'http://localhost/test'
        ]);

        $result = $this->middleware->process($request, $handler);
        $this->assertSame($response, $result);
    }

    public function testBlocksDisallowedOrigin(): void
    {
        $request = $this->createRequest(['Origin' => 'http://evil.com']);
        $response = new Response();
        $handler = $this->createHandler($response);
    
        $this->logger->expectLogInfo(sprintf(
            'CORS blocked - Origin %s not allowed. URI: %s',
            'http://evil.com',
            'http://localhost/test'
        ));
    
        $result = $this->middleware->process($request, $handler);
        $logBody = json_decode((string)$result->getBody(), true);
    
        $this->assertEquals(ErrorMessages::get('corsOriginNotAllowed')['statusCode'], $result->getStatusCode());
        $this->assertEquals(
            ['errors' => [ErrorMessages::get('corsOriginNotAllowed')]],
            $logBody
        );
    }

    public function testAllowsWhitelistedOrigin(): void
    {
        $request = $this->createRequest(['Origin' => 'http://localhost:8080']);
        $response = new Response();
        $handler = $this->createHandler($response);

        $result = $this->middleware->process($request, $handler);
        
        $this->assertEquals('http://localhost:8080', $result->getHeaderLine('Access-Control-Allow-Origin'));
        $this->assertNotEmpty($result->getHeaderLine('Access-Control-Allow-Methods'));
    }

    public function testHandlesPreflightRequest(): void
    {
        $headers = new \Slim\Psr7\Headers([
            'Origin' => 'http://localhost:8080',
            'Access-Control-Request-Method' => 'POST',
            'Access-Control-Request-Headers' => 'content-type'
        ]);
        
        $request = new \Slim\Psr7\Request(
            'OPTIONS',
            new \Slim\Psr7\Uri('http', 'localhost/test'),
            $headers,
            [],
            [],
            new \Slim\Psr7\Stream(fopen('php://temp', 'r+'))
        );
        
        $response = new Response();
        $handler = $this->createHandler($response);
    
        $result = $this->middleware->process($request, $handler);
        
        $this->assertEquals('http://localhost:8080', $result->getHeaderLine('Access-Control-Allow-Origin'));
        $this->assertNotEmpty($result->getHeaderLine('Access-Control-Allow-Methods'));
        $this->assertNotEmpty($result->getHeaderLine('Access-Control-Allow-Headers'));
    }
}