<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Tests\Middleware;

use BO\Zmscitizenapi\Middleware\MaintenanceMiddleware;
use BO\Zmscitizenapi\Tests\MiddlewareTestCase;
use BO\Zmscitizenapi\Utils\ErrorMessages;
use BO\Zmsclient\Psr7\Uri;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

class MaintenanceMiddlewareTest extends MiddlewareTestCase
{
    private bool $previousMaintenanceMode;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->previousMaintenanceMode = \App::$MAINTENANCE_MODE_ENABLED;
    }

    #[\Override]
    protected function tearDown(): void
    {
        \App::$MAINTENANCE_MODE_ENABLED = $this->previousMaintenanceMode;
        parent::tearDown();
    }

    public function testBlocksRequestsWhileMaintenanceIsEnabled(): void
    {
        \App::$MAINTENANCE_MODE_ENABLED = true;
        $middleware = new MaintenanceMiddleware();
        $request = $this->requestForPath('/offices-and-services/');
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->never())->method('handle');

        $result = $middleware->process($request, $handler);
        $responseData = json_decode((string) $result->getBody(), true);

        $this->assertSame(503, $result->getStatusCode());
        $this->assertSame(
            ['errors' => [ErrorMessages::get('serviceUnavailable')]],
            $responseData
        );
    }

    public function testAllowsSourceCacheWarmupWhileMaintenanceIsEnabled(): void
    {
        \App::$MAINTENANCE_MODE_ENABLED = true;
        $middleware = new MaintenanceMiddleware();
        $response = new Response();
        $request = $this->requestForPath('/terminvereinbarung/api/citizen/source-cache/warmup/');
        $handler = $this->createStub(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($response);

        $result = $middleware->process($request, $handler);

        $this->assertSame($response, $result);
    }

    public function testAllowsRequestsWhenMaintenanceIsDisabled(): void
    {
        if (\App::MAINTENANCE_MODE_ENABLED) {
            $this->markTestSkipped('App::MAINTENANCE_MODE_ENABLED is fixed true in this process.');
        }

        \App::$MAINTENANCE_MODE_ENABLED = false;
        $middleware = new MaintenanceMiddleware();
        $response = new Response();
        $request = $this->requestForPath('/offices-and-services/');
        $handler = $this->createStub(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($response);

        $result = $middleware->process($request, $handler);

        $this->assertSame($response, $result);
    }

    private function requestForPath(string $path): ServerRequestInterface
    {
        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getUri')->willReturn(new Uri('http://localhost' . $path));

        return $request;
    }
}
