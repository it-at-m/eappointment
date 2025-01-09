<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Tests;

use BO\Zmscitizenapi\Tests\TestLogger;
use BO\Zmsclient\Psr7\Uri;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Factory\ResponseFactory;

abstract class MiddlewareTestCase extends TestCase
{
    protected TestLogger $logger;
    protected ResponseFactory $responseFactory;

    protected function setUp(): void
    {
        parent::setUp();
        TestLogger::initTest($this);
        $this->logger = new TestLogger();  // Create instance instead of using class name
        $this->responseFactory = new ResponseFactory();
    }
    
    protected function tearDown(): void
    {
        TestLogger::verifyNoMoreLogs();
        TestLogger::resetTest();
        parent::tearDown();
    }

    protected function createRequest(array $headers = []): ServerRequestInterface
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getHeaders')->willReturn($headers);
        $request->method('getHeaderLine')->willReturnCallback(
            function ($name) use ($headers) {
                return $headers[$name] ?? '';
            }
        );
        $request->method('getUri')->willReturn(new Uri('http://localhost/test'));
        return $request;
    }

    protected function createHandler(ResponseInterface $response = null): RequestHandlerInterface
    {
        $handler = $this->createMock(RequestHandlerInterface::class);
        if ($response) {
            $handler->method('handle')->willReturn($response);
        }
        return $handler;
    }

}