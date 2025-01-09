<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Tests\Middleware;

use BO\Zmscitizenapi\Localization\ErrorMessages;
use BO\Zmscitizenapi\Middleware\RequestSanitizerMiddleware;
use BO\Zmscitizenapi\Tests\MiddlewareTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Response;

class RequestSanitizerMiddlewareTest extends MiddlewareTestCase
{
    private RequestSanitizerMiddleware $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        \App::$source_name = 'unittest';

        if (\App::$cache) {
            \App::$cache->clear();
        }
        $this->middleware = new RequestSanitizerMiddleware($this->logger);
    }

    public function testSanitizesQueryParams(): void
    {
        /** @var ServerRequestInterface|MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
            ->method('getQueryParams')
            ->willReturn(['test' => '<script>alert(1)</script>']);
        $request->expects($this->once())
            ->method('withQueryParams')
            ->willReturnSelf();
        $request->expects($this->any())
            ->method('getUri')
            ->willReturn(new \BO\Zmsclient\Psr7\Uri('http://localhost/test'));
        
        $response = new Response();
        $handler = $this->createHandler($response);

        $this->logger->expectLogInfo('Request sanitized', [
            'uri' => 'http://localhost/test'
        ]);

        $result = $this->middleware->process($request, $handler);
        $this->assertSame($response, $result);
    }

    public function testHandlesSanitizationError(): void
    {
        /** @var ServerRequestInterface|MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
            ->method('getQueryParams')
            ->willThrowException(new \RuntimeException('Sanitization error'));
        $request->expects($this->any())
            ->method('getUri')
            ->willReturn(new \BO\Zmsclient\Psr7\Uri('http://localhost/test'));
            
        $response = new Response();
        $handler = $this->createHandler($response);
    
        $this->logger->expectLogError(new \RuntimeException('Sanitization error'));
    
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Sanitization error');
        
        $this->middleware->process($request, $handler);
    }
}