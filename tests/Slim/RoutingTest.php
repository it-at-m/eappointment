<?php

namespace BO\Slim\Tests;

use \BO\Slim\Render;

class RoutingTest extends Base
{
    protected $classname = "Get";

    public function testRendering()
    {
        $this->assertEmpty(\BO\Slim\Bootstrap::loadRouting(\App::APP_PATH . '/Slim/routing.php'));
    }

    public function testErrorHandler()
    {
        $request = static::createBasicRequest('GET', '/unittest/1234/');
        $exception = new \Exception;
        $container = \App::$slim->getContainer()->get('errorHandler');
        $response = $container($request, $this->getResponse(), $exception);
        $this->assertStringContainsString('Slim Application Error', (string)$response->getBody());
    }
}
