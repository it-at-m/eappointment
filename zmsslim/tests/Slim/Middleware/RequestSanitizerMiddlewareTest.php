<?php

declare(strict_types=1);

namespace BO\Slim\Tests\Middleware;

use BO\Slim\Middleware\RequestSanitizerMiddleware;
use BO\Slim\Tests\TestLogger;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Response;

class RequestSanitizerMiddlewareTest extends MiddlewareTestCase
{
    private RequestSanitizerMiddleware $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new RequestSanitizerMiddleware($this->logger, 10, 32768);
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
            ->willReturn(new \Slim\Psr7\Uri('http', 'localhost', 80, '/test'));

        $response = new Response();
        $handler = $this->createHandler($response);

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
            ->willReturn(new \Slim\Psr7\Uri('http', 'localhost', 80, '/test'));

        $response = new Response();
        $handler = $this->createHandler($response);

        TestLogger::expectLogError(new \RuntimeException('Sanitization error'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Sanitization error');

        $this->middleware->process($request, $handler);
    }
}
