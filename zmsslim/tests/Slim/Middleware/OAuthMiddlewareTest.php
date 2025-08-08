<?php

namespace BO\Slim\Tests\Middleware;

use BO\Slim\Middleware\OAuthMiddleware;
use BO\Slim\Middleware\OAuth\KeycloakInstance;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @SuppressWarnings(PHPMD)
 */
class OAuthMiddlewareTest extends TestCase
{
    protected $middleware;
    protected $request;
    protected $handler;

    protected function setUp(): void
    {
        $this->middleware = new OAuthMiddleware('login');
        $this->request = $this->createMock(ServerRequestInterface::class);
        $this->handler = $this->createMock(RequestHandlerInterface::class);
    }

    public function testAuthInstancesOnlyContainsKeycloak()
    {
        $this->assertArrayHasKey('keycloak', OAuthMiddleware::$authInstances);
        $this->assertArrayNotHasKey('gitlab', OAuthMiddleware::$authInstances);
        $this->assertEquals('\BO\Slim\Middleware\OAuth\KeycloakInstance', OAuthMiddleware::$authInstances['keycloak']);
    }

    public function testHandleLoginWithKeycloakProvider()
    {
        $this->request
            ->method('getQueryParams')
            ->willReturn(['provider' => 'keycloak']);

        $this->request
            ->method('getParam')
            ->willReturnMap([
                ['code', 'test-code'],
                ['state', 'test-state']
            ]);

        $this->request
            ->method('getAttribute')
            ->with('authentificationHandler')
            ->willReturn('login');

        $response = $this->createMock(ResponseInterface::class);
        $this->handler
            ->method('handle')
            ->willReturn($response);

        $result = $this->middleware->__invoke($this->request, $this->handler);

        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    public function testHandleLoginWithoutProvider()
    {
        $this->request
            ->method('getQueryParams')
            ->willReturn([]);

        $response = $this->createMock(ResponseInterface::class);
        $this->handler
            ->method('handle')
            ->willReturn($response);

        $result = $this->middleware->__invoke($this->request, $this->handler);

        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    public function testHandleLogout()
    {
        $this->middleware = new OAuthMiddleware('logout');

        $this->request
            ->method('getQueryParams')
            ->willReturn(['provider' => 'keycloak']);

        $this->request
            ->method('getParam')
            ->willReturn(null);

        $this->request
            ->method('getAttribute')
            ->with('authentificationHandler')
            ->willReturn('logout');

        $response = $this->createMock(ResponseInterface::class);
        $this->handler
            ->method('handle')
            ->willReturn($response);

        $result = $this->middleware->__invoke($this->request, $this->handler);

        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    public function testHandleRefreshToken()
    {
        $this->middleware = new OAuthMiddleware('refresh');

        $this->request
            ->method('getQueryParams')
            ->willReturn(['provider' => 'keycloak']);

        $this->request
            ->method('getAttribute')
            ->with('authentificationHandler')
            ->willReturn('refresh');

        $response = $this->createMock(ResponseInterface::class);
        $this->handler
            ->method('handle')
            ->willReturn($response);

        $result = $this->middleware->__invoke($this->request, $this->handler);

        $this->assertInstanceOf(ResponseInterface::class, $result);
    }
} 