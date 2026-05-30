<?php

declare(strict_types=1);

namespace BO\Slim\Tests\Middleware;

use BO\Slim\Middleware\SecurityHeadersMiddleware;
use BO\Slim\Tests\TestLogger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Response;

class SecurityHeadersMiddlewareTest extends MiddlewareTestCase
{
    private SecurityHeadersMiddleware $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new SecurityHeadersMiddleware(
            $this->logger,
            null,
            static function (\Throwable $e, ServerRequestInterface $request): ResponseInterface {
                $response = new Response();
                $response->getBody()->write(json_encode(['error' => 'securityHeaderViolation']));
                return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
            }
        );
    }

    public function testAddsSecurityHeaders(): void
    {
        $request = $this->createRequest(['X-Test' => 'test']);
        $response = new Response();
        $handler = $this->createHandler($response);

        $result = $this->middleware->process($request, $handler);

        $this->assertContainsEquals('DENY', $result->getHeader('X-Frame-Options'));
        $this->assertContainsEquals('nosniff', $result->getHeader('X-Content-Type-Options'));
    }

    public function testHandlesHeaderException(): void
    {
        $request = $this->createRequest(['X-Test' => 'test']);
        $response = $this->createMock(Response::class);
        $response->method('withHeader')
            ->willThrowException(new \RuntimeException('Header error'));
        $handler = $this->createHandler($response);

        TestLogger::expectLogError(new \RuntimeException('Header error'));

        $result = $this->middleware->process($request, $handler);

        $this->assertEquals(500, $result->getStatusCode());
        $this->assertEquals(
            ['error' => 'securityHeaderViolation'],
            json_decode((string) $result->getBody(), true)
        );
    }
}
