<?php
/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

declare(strict_types=1);

namespace BO\Slim\Tests\Middleware;

use BO\Slim\Container;
use BO\Slim\Middleware;
use BO\Slim\Request;
use PHPUnit\Framework\TestCase;
use Slim\CallableResolver;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Headers;
use Slim\Psr7\Stream;
use Slim\Psr7\Uri;
use Slim\Routing;

class RouteTest extends TestCase
{
    public function testGetInfo()
    {
        $uri = new Uri('http', 'localhost', 80, '/admin');
        $request = new Request('GET', $uri, new Headers([]), [], [], new Stream(fopen('php://temp', 'wb+')));
        $container = new Container();
        $nextHandler = new RequestHandlerMock();
        $callable = function () {
            return 'I found it.';
        };

        $route = new Routing\Route(
            ['GET'],
            '\treasure',
            $callable,
            new ResponseFactory(),
            new CallableResolver($container),
            $container
        );
        $route->setName('pathToTreasure');

        $sut = new Middleware\Route($container);
        $sut->getInfo(
            $request->withAttribute(Routing\RouteContext::ROUTE, $route),
            $nextHandler
        );

        self::assertTrue($container->has('currentRoute'));
        self::assertTrue($container->has('currentRouteParams'));
        self::assertSame('pathToTreasure', $container->get('currentRoute'));
    }
}
