<?php
/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

declare(strict_types=1);

namespace BO\Slim\Tests\Middleware;

use BO\Slim\Middleware\SubPath;
use BO\Slim\Request;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Headers;
use Slim\Psr7\Stream;
use Slim\Psr7\Uri;

class SubPathTest extends TestCase
{
    public function testInvoke()
    {
        $uri = new Uri('http', 'localhost', 80, '/admin/account/');
        $serverParams = [
            'REQUEST_URI' => '/admin/account/',
            'SCRIPT_NAME' => '/admin/index.php',
        ];
        $request = new Request('GET', $uri, new Headers([]), [], $serverParams, new Stream(fopen('php://temp', 'wb+')));
        $nextHandler = new RequestHandlerMock();

        $sut = new SubPath();
        $sut->__invoke($request, $nextHandler);

        self::assertSame('/account/', $nextHandler->getRequest()->getUri()->getPath());
    }
}
