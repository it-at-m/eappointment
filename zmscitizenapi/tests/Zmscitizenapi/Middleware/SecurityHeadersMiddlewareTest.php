<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Tests\Middleware;

use BO\Zmscitizenapi\Localization\ErrorMessages;
use BO\Zmscitizenapi\Middleware\SecurityHeadersMiddleware;
use BO\Zmscitizenapi\Tests\MiddlewareTestCase;
use Slim\Psr7\Response;

class SecurityHeadersMiddlewareTest extends MiddlewareTestCase
{
    private SecurityHeadersMiddleware $middleware;

    /*protected function setUp(): void
    {
        parent::setUp();
        \App::$source_name = 'unittest';

        if (\App::$cache) {
            \App::$cache->clear();
        }
        $this->middleware = new SecurityHeadersMiddleware($this->logger);
    }*/

    /*public function testAddsSecurityHeaders(): void
    {
        $request = $this->createRequest(['X-Test' => 'test']);
        $response = new Response();
        $handler = $this->createHandler($response);*/

        /*$this->logger->expectLogInfo('Security headers added', [
            'uri' => 'http://localhost/test'
        ]);*/

        /*$result = $this->middleware->process($request, $handler);
        
        $this->assertContainsEquals('DENY', $result->getHeader('X-Frame-Options'));
        $this->assertContainsEquals('nosniff', $result->getHeader('X-Content-Type-Options'));
    }*/

    /*public function testHandlesHeaderException(): void
    {
        $request = $this->createRequest(['X-Test' => 'test']);
        $response = $this->createMock(Response::class);
        $response->method('withHeader')
            ->willThrowException(new \RuntimeException('Header error'));
        $handler = $this->createHandler($response);
    
        $this->logger->expectLogError(new \RuntimeException('Header error'));
    
        $result = $this->middleware->process($request, $handler);

        $logBody = json_decode((string)$result->getBody(), true);
    
        $this->assertEquals(ErrorMessages::get('securityHeaderViolation')['statusCode'], $result->getStatusCode());
        $this->assertEquals(
            ['errors' => [ErrorMessages::get('securityHeaderViolation')]],
            $logBody
        );
    }*/
}
