<?php
/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

declare(strict_types=1);

namespace BO\Zmsapi\Tests\Helper;

use BO\Slim\Request;
use BO\Slim\Tests\Middleware\RequestHandlerMock;
use BO\Zmsapi\Helper\TransactionMiddleware;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Headers;
use Slim\Psr7\Stream;
use Slim\Psr7\Uri;

class TransactionMiddlewareTest extends TestCase
{
    public function testInvoke()
    {
        $uri = new Uri('http', 'localhost', 80, '/admin/account/', '', '', 'username', 'secret');
        $request = new Request('GET', $uri, new Headers([]), [], [], new Stream(fopen('php://temp', 'wb+')));
        $nextHandler = new RequestHandlerMock();

        $sut = new TransactionMiddleware();
        $sut->__invoke($request, $nextHandler);

        self::assertNotNull(\BO\Zmsdb\Connection\Select::writeRollback());
    }
}