<?php
/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

declare(strict_types=1);

namespace BO\Slim\Tests\Middleware;

use BO\Slim\Middleware\IpAddress;
use BO\Slim\Request;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Headers;
use Slim\Psr7\Stream;
use Slim\Psr7\Uri;

class IpAddressTest extends TestCase
{
    public function testInvoke()
    {
        $uri = new Uri('http', 'localhost', 80, '/admin/account/');
        $serverParams = [
            'REMOTE_ADDR' => '0.12.34.56',
        ];
        $request = new Request('GET', $uri, new Headers([]), [], $serverParams, new Stream(fopen('php://temp', 'wb+')));
        $nextHandler = new RequestHandlerMock();

        $sut = new IpAddress(true);
        $sut->__invoke($request, $nextHandler);

        self::assertSame('0.12.34.56', $nextHandler->getRequest()->getAttribute('ip_address'));

        $headers = new Headers(['X-Remote-Ip' => '10.20.30.40']);
        $request = new Request('GET', $uri, $headers, [], $serverParams, new Stream(fopen('php://temp', 'wb+')));

        $sut->__invoke($request, $nextHandler);

        self::assertSame('10.20.30.40', $nextHandler->getRequest()->getAttribute('ip_address'));
    }
}
