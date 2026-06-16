<?php

declare(strict_types=1);

namespace BO\Slim\Tests\Middleware;

use BO\Slim\Tests\TestLogger;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Uri;

abstract class MiddlewareTestCase extends TestCase
{
    protected TestLogger $logger;
    protected ResponseFactory $responseFactory;

    protected function setUp(): void
    {
        parent::setUp();
        TestLogger::initTest($this);
        $this->logger = new TestLogger();
        $this->responseFactory = new ResponseFactory();
    }

    protected function tearDown(): void
    {
        TestLogger::verifyNoMoreLogs();
        TestLogger::resetTest();
        parent::tearDown();
    }

    protected function createRequest(array $headerValues = []): ServerRequestInterface
    {
        $headers = [];
        foreach ($headerValues as $name => $value) {
            $headers[$name] = (array) $value;
        }

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getHeaders')->willReturn($headers);
        $request->method('getHeaderLine')->willReturnCallback(
            function ($name) use ($headers) {
                if (isset($headers[$name])) {
                    return implode(', ', $headers[$name]);
                }

                return '';
            }
        );
        $request->method('getUri')->willReturn(new Uri('http', 'localhost', 80, '/test'));
        return $request;
    }

    protected function createHandler(ResponseInterface $response): RequestHandlerInterface
    {
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($response);
        return $handler;
    }
}
