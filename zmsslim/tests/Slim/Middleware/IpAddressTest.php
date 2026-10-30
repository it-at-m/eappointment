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

        $sut = new IpAddress(true, true);
        $sut->__invoke($request, $nextHandler);

        self::assertSame('0.12.34.56', $nextHandler->getRequest()->getAttribute('ip_address'));

        $headers = new Headers([
            'X-Remote-Ip' => '10.20.30.40',
            'X-Forwarded-For' => '198.51.100.1',
        ]);
        $request = new Request('GET', $uri, $headers, [], $serverParams, new Stream(fopen('php://temp', 'wb+')));

        $sut->__invoke($request, $nextHandler);

        self::assertSame('0.12.34.56', $nextHandler->getRequest()->getAttribute('ip_address'));

        $gatewayParams = [
            'REMOTE_ADDR' => '10.0.0.5',
        ];
        $gatewayHeaders = new Headers([
            'X-Forwarded-For' => '198.51.100.1, 203.0.113.20',
            'Client-Ip' => '198.51.100.99',
        ]);
        $request = new Request(
            'GET',
            $uri,
            $gatewayHeaders,
            [],
            $gatewayParams,
            new Stream(fopen('php://temp', 'wb+'))
        );

        $sut->__invoke($request, $nextHandler);

        self::assertSame('203.0.113.20', $nextHandler->getRequest()->getAttribute('ip_address'));
    }
}
