<?php
/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

declare(strict_types=1);

namespace BO\Slim\Tests\Middleware;

use BO\Slim\Middleware\TrailingSlash;
use BO\Slim\Request;
use Fig\Http\Message\StatusCodeInterface;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Headers;
use Slim\Psr7\Stream;
use Slim\Psr7\Uri;

class TrailingSlashTest extends TestCase
{
    public function testInvokeRedirectsNonApiPaths()
    {
        $uri = new Uri('http', 'localhost', 80, '/admin');

        $request = new Request('GET', $uri, new Headers([]), [], [], new Stream(fopen('php://temp', 'wb+')));
        $nextHandler = new RequestHandlerMock();

        $sut = new TrailingSlash();
        $response = $sut->__invoke($request, $nextHandler);

        self::assertTrue($response->hasHeader('Location'));
        self::assertSame(StatusCodeInterface::STATUS_MOVED_PERMANENTLY, $response->getStatusCode());
        self::assertContainsEquals('//localhost/admin/', $response->getHeader('Location'));
    }

    public function testInvokeDoesNotForceHttpsWhenXSslIsNo()
    {
        $uri = new Uri('http', 'localhost', 80, '/admin');

        $request = new Request(
            'GET',
            $uri,
            new Headers(['X-Ssl' => ['no']]),
            [],
            [],
            new Stream(fopen('php://temp', 'wb+'))
        );
        $nextHandler = new RequestHandlerMock();

        $sut = new TrailingSlash();
        $response = $sut->__invoke($request, $nextHandler);

        self::assertSame(StatusCodeInterface::STATUS_MOVED_PERMANENTLY, $response->getStatusCode());
        self::assertContainsEquals('//localhost/admin/', $response->getHeader('Location'));
    }

    public function testInvokeForcesHttpsWhenXSslIsPresent()
    {
        $uri = new Uri('http', 'localhost', 80, '/admin');

        $request = new Request(
            'GET',
            $uri,
            new Headers(['X-Ssl' => ['yes']]),
            [],
            [],
            new Stream(fopen('php://temp', 'wb+'))
        );
        $nextHandler = new RequestHandlerMock();

        $sut = new TrailingSlash();
        $response = $sut->__invoke($request, $nextHandler);

        self::assertSame(StatusCodeInterface::STATUS_MOVED_PERMANENTLY, $response->getStatusCode());
        self::assertContainsEquals('https://localhost:80/admin/', $response->getHeader('Location'));
    }

    public function testInvokeRewritesApiPathsWithoutRedirect()
    {
        $uri = new Uri('http', 'localhost', 80, '/terminvereinbarung/api/2/status');

        $request = new Request('GET', $uri, new Headers([]), [], [], new Stream(fopen('php://temp', 'wb+')));
        $nextHandler = new RequestHandlerMock();

        $sut = new TrailingSlash();
        $response = $sut->__invoke($request, $nextHandler);

        self::assertFalse($response->hasHeader('Location'));
        self::assertSame(200, $response->getStatusCode());
        self::assertSame(
            '/terminvereinbarung/api/2/status/',
            $nextHandler->getRequest()->getUri()->getPath()
        );
    }

    public function testInvokeLeavesApiPathsWithTrailingSlashUnchanged()
    {
        $uri = new Uri('http', 'localhost', 80, '/terminvereinbarung/api/2/status/');

        $request = new Request('GET', $uri, new Headers([]), [], [], new Stream(fopen('php://temp', 'wb+')));
        $nextHandler = new RequestHandlerMock();

        $sut = new TrailingSlash();
        $sut->__invoke($request, $nextHandler);

        self::assertSame(
            '/terminvereinbarung/api/2/status/',
            $nextHandler->getRequest()->getUri()->getPath()
        );
    }
}
