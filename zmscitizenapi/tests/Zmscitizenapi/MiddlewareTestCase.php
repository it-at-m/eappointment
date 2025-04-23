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

    protected function createRequest(array $headerValues = []): ServerRequestInterface
    {
        // Normalize headers to PSR-7 format: header name => array of values
        $headers = [];
        foreach ($headerValues as $name => $value) {
            $headers[$name] = (array)$value;
        }

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getHeaders')->willReturn($headers);
        $request->method('getHeaderLine')->willReturnCallback(
            function ($name) use ($headers) {
                if (isset($headers[$name])) {
                    return implode(', ', $headers[$name]);
                } else {
                    return '';
                }
            }
        );
        $request->method('getUri')->willReturn(new Uri('http://localhost/test'));
        return $request;
    }

    protected function createHandler(ResponseInterface $response): RequestHandlerInterface
    {
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($response);
        return $handler;
    }
}