<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Tests\Middleware;

use BO\Zmscitizenapi\Localization\ErrorMessages;
use BO\Zmscitizenapi\Middleware\CsrfMiddleware;
use BO\Zmscitizenapi\Tests\MiddlewareTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Response;

class CsrfMiddlewareTest extends MiddlewareTestCase
{
    private CsrfMiddleware $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        \App::$source_name = 'unittest';

        if (\App::$cache) {
            \App::$cache->clear();
        }
        $this->middleware = new CsrfMiddleware($this->logger);
    }

    public function testAllowsGetRequest(): void
    {
        /** @var ServerRequestInterface|MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
            ->method('getMethod')
            ->willReturn('GET');
        $request->expects($this->any())
            ->method('getUri')
            ->willReturn(new \BO\Zmsclient\Psr7\Uri('http://localhost/test'));
            
        $response = new Response();
        $handler = $this->createHandler($response);

        $result = $this->middleware->process($request, $handler);
        $this->assertSame($response, $result);
    }

    // Todo: Uncomment when CSRF is renabled
    // public function testBlocksPostWithoutToken(): void
    // {
    //     // @var ServerRequestInterface|MockObject $request
    //     $request = $this->createMock(ServerRequestInterface::class);
    //     $request->expects($this->once())
    //         ->method('getMethod')
    //         ->willReturn('POST');
    //     $request->expects($this->any())
    //         ->method('getUri')
    //         ->willReturn(new \BO\Zmsclient\Psr7\Uri('http://localhost/test'));
    //         
    //     $response = new Response();
    //     $handler = $this->createHandler($response);

    //     $this->logger->expectLogInfo('CSRF token missing', [
    //         'uri' => 'http://localhost/test'
    //     ]);

    //     $result = $this->middleware->process($request, $handler);
    //     $logBody = json_decode((string)$result->getBody(), true);

    //     $this->assertEquals(ErrorMessages::get('csrfTokenMissing')['statusCode'], $result->getStatusCode());
    //     $this->assertEquals(
    //         ['errors' => [ErrorMessages::get('csrfTokenMissing')]],
    //         $logBody
    //     );
    // }

    // Todo: Uncomment when CSRF is renabled
    // public function testBlocksInvalidToken(): void
    // {
    //     /** @var ServerRequestInterface|MockObject $request */
    //     $request = $this->createMock(ServerRequestInterface::class);
    //     $request->expects($this->once())
    //         ->method('getMethod')
    //         ->willReturn('POST');
    //     $request->method('getHeaderLine')
    //         ->willReturnCallback(function(string $header) {
    //             $this->assertEquals('X-CSRF-Token', $header);
    //             return 'invalid';
    //         });
    //     $request->expects($this->any())
    //         ->method('getUri')
    //         ->willReturn(new \BO\Zmsclient\Psr7\Uri('http://localhost/test'));
            
    //     $response = new Response();
    //     $handler = $this->createHandler($response);

    //     $this->logger->expectLogInfo('Invalid CSRF token');

    //     $result = $this->middleware->process($request, $handler);
    //     $logBody = json_decode((string)$result->getBody(), true);

    //     $this->assertEquals(ErrorMessages::get('csrfTokenInvalid')['statusCode'], $result->getStatusCode());
    //     $this->assertEquals(
    //         ['errors' => [ErrorMessages::get('csrfTokenInvalid')]],
    //         $logBody
    //     );
    // }

}