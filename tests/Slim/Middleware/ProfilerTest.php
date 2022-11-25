<?php
/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

declare(strict_types=1);

namespace BO\Slim\Tests\Middleware;

use BO\Slim\Middleware\Profiler;
use BO\Slim\Request;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Headers;
use Slim\Psr7\Stream;
use Slim\Psr7\Uri;

class ProfilerTest extends TestCase
{
    public function testInvoke()
    {
        $uri = new Uri('http', 'localhost', 80, '/admin/account/');
        $request = new Request('GET', $uri, new Headers([]), [], [], new Stream(fopen('php://temp', 'wb+')));
        $nextHandler = new RequestHandlerMock();

        $sut = new Profiler();
        $response = $sut->__invoke($request, $nextHandler);

        self::assertTrue($response->hasHeader('X-Profiling'));
    }
}
