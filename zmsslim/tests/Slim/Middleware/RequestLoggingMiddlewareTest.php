<?php

declare(strict_types=1);

namespace BO\Slim\Tests\Middleware;

use BO\Slim\Middleware\RequestLoggingMiddleware;
use BO\Slim\Tests\TestLogger;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Response;

class RequestLoggingMiddlewareTest extends MiddlewareTestCase
{
    private RequestLoggingMiddleware $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new RequestLoggingMiddleware($this->logger);
    }

    public function testLogsSuccessfulRequest(): void
    {
        /** @var ServerRequestInterface|MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn(new \Slim\Psr7\Uri('http', 'localhost', 80, '/test'));
        $request->method('getMethod')->willReturn('GET');
        $request->method('getHeaders')->willReturn([]);
        $request->method('getHeaderLine')->willReturn('');
        $request->method('getQueryParams')->willReturn([]);

        $response = new Response(200);
        $handler = $this->createHandler($response);

        $result = $this->middleware->process($request, $handler);
        $this->assertSame($response, $result);
    }

    public function testLogsAndRethrowsHandlerException(): void
    {
        /** @var ServerRequestInterface|MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn(new \Slim\Psr7\Uri('http', 'localhost', 80, '/test'));

        $handler = $this->createMock(\Psr\Http\Server\RequestHandlerInterface::class);
        $handler->method('handle')->willThrowException(new \RuntimeException('Handler failed'));

        TestLogger::expectLogError(new \RuntimeException('Handler failed'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Handler failed');

        $this->middleware->process($request, $handler);
    }
}
