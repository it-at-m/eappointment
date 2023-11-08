<?php

namespace BO\Slim\Tests;

use \BO\Slim\Render;
use Slim\Handlers\ErrorHandler;
use Slim\Routing\RouteCollector;

class RoutingTest extends Base
{
    protected $classname = "Get";

    public function testRendering()
    {
        \BO\Slim\Bootstrap::loadRouting(\App::APP_PATH . '/Slim/routing.php');
        /** @var RouteCollector $router */
        $router = \App::$slim->getContainer()->get('router');
        $this->assertEquals('getroute', $router->getNamedRoute('getroute')->getName());
    }

    public function testErrorHandler()
    {
        $request = static::createBasicRequest('GET', '/unittest/1234/');
        $exception = new \Exception;
        /** @var ErrorHandler $errorHandler */
        $errorHandler = \App::$slim->getContainer()->get('errorMiddleware')->getDefaultErrorHandler();
        $response = $errorHandler($request, $exception, true, false, false);
        $this->assertStringContainsString('Slim Application Error', (string)$response->getBody());
    }

    public function test404ErrorHandler()
    {
        $request = self::createBasicRequest('GET', '/notfound/');
        $exception = new \Slim\Exception\HttpNotFoundException($request);
        /** @var ErrorHandler $errorHandler */
        $errorHandler = \App::$slim->getContainer()->get('errorMiddleware')->getDefaultErrorHandler();
        $response = $errorHandler($request, $exception, true, false, false);
        $this->assertStringContainsString('<h1>404 Not Found</h1>', (string)$response->getBody());
    }
}
