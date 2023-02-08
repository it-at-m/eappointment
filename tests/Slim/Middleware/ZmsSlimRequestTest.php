<?php
/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

declare(strict_types=1);

namespace BO\Slim\Tests\Middleware;

use BO\Slim\Request as ZmsSlimRequest;
use Slim\Psr7\Request as SlimRequest;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Headers;
use Slim\Psr7\Stream;
use Slim\Psr7\Uri;

class ZmsSlimRequestTest extends TestCase
{
    public function testInvoke()
    {
        $uri = new Uri('http', 'localhost', 80, '/admin/account/');
        $request = (new SlimRequest('GET', $uri, new Headers([]), [], [], new Stream(fopen('php://temp', 'wb+'))))
            ->withAttribute('myAttribute', 'myValue')
            ->withParsedBody(['theTruth' => 42]);
        $nextHandler = new RequestHandlerMock();

        $sut = new \BO\Slim\Middleware\ZmsSlimRequest();
        $sut($request, $nextHandler);

        self::assertInstanceOf(ZmsSlimRequest::class, $nextHandler->getRequest());
        self::assertSame('myValue', $nextHandler->getRequest()->getAttribute('myAttribute'));
        self::assertSame(['theTruth' => 42], $nextHandler->getRequest()->getParsedBody());
    }
}