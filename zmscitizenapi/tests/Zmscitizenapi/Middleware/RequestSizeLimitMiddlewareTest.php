<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Tests\Middleware;

use BO\Zmscitizenapi\Localization\ErrorMessages;
use BO\Zmscitizenapi\Middleware\RequestSizeLimitMiddleware;
use BO\Zmscitizenapi\Tests\MiddlewareTestCase;
use Slim\Psr7\Response;

class RequestSizeLimitMiddlewareTest extends MiddlewareTestCase
{
    private RequestSizeLimitMiddleware $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        \App::$source_name = 'unittest';

        if (\App::$cache) {
            \App::$cache->clear();
        }
        $this->middleware = new RequestSizeLimitMiddleware($this->logger);
    }

    public function testAllowsRequestUnderLimit(): void
    {
        $request = $this->createRequest(['Content-Length' => '1024']);
        $response = new Response();
        $handler = $this->createHandler($response);

        $result = $this->middleware->process($request, $handler);
        $this->assertSame($response, $result);
    }

    public function testBlocksRequestOverLimit(): void
    {
        $request = $this->createRequest(['Content-Length' => '20000000']);
        $response = new Response();
        $handler = $this->createHandler($response);
    
        $this->logger->expectLogInfo(sprintf(
            'Request too large: %d bytes. URI: %s',
            20000000,
            'http://localhost/test'
        ));
    
        $result = $this->middleware->process($request, $handler);
        $logBody = json_decode((string)$result->getBody(), true);
    
        $this->assertEquals(ErrorMessages::get('requestEntityTooLarge')['statusCode'], $result->getStatusCode());
        $this->assertEquals(
            ['errors' => [ErrorMessages::get('requestEntityTooLarge')]],
            $logBody
        );
    }
}