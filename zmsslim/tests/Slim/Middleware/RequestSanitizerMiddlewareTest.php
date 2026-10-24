<?php

declare(strict_types=1);

namespace BO\Slim\Tests\Middleware;

use BO\Slim\Middleware\RequestSanitizerMiddleware;
use BO\Slim\Tests\TestLogger;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Headers;
use Slim\Psr7\Request as SlimRequest;
use Slim\Psr7\Response;
use Slim\Psr7\Stream;
use Slim\Psr7\Uri;

class RequestSanitizerMiddlewareTest extends MiddlewareTestCase
{
    private RequestSanitizerMiddleware $middleware;

    #[\Override]
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

    public function testKeepsNewlineAndTabAndStripsOtherControls(): void
    {
        $uri = new Uri('http', 'localhost', 80, '/process/queue/');
        $request = (new SlimRequest('POST', $uri, new Headers([]), [], [], new Stream(fopen('php://temp', 'wb+'))))
            ->withParsedBody([
                'customTextfield' => "ATAF\n\tZeile\x00\x0B",
                'padded' => '  leading',
            ]);
        $handler = new RequestHandlerMock();

        $this->middleware->process($request, $handler);

        $body = $handler->getRequest()->getParsedBody();
        $this->assertSame("ATAF\n\tZeile", $body['customTextfield']);
        $this->assertSame('leading', $body['padded']);
    }

    public function testRejectsNonPositiveLimits(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('maxRecursionDepth must be greater than 0');

        new RequestSanitizerMiddleware($this->logger, 0, 32768);
    }
}
