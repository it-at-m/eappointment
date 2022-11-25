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
    public function testInvoke()
    {
        $uri = new Uri('http', 'localhost', 80, '/admin');

        $request = new Request('GET', $uri, new Headers([]), [], [], new Stream(fopen('php://temp', 'wb+')));
        $nextHandler = new RequestHandlerMock();

        $sut = new TrailingSlash();
        $response = $sut->__invoke($request, $nextHandler);

        self::assertTrue($response->hasHeader('Location'));
        self::assertSame(StatusCodeInterface::STATUS_MOVED_PERMANENTLY, $response->getStatusCode());
        self::assertContains('//localhost/admin/', $response->getHeader('Location'));
    }
}
