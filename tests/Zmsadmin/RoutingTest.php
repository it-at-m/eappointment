<?php

namespace BO\Zmsadmin\Tests;

use \BO\Slim\Render;

class RoutingTest extends Base
{
    public function testRendering()
    {
        \BO\Slim\Bootstrap::loadRouting(\App::APP_PATH . '/routing.php');

        $response = \App::$slim->getContainer()
            ->offsetSet('errorHandler',
            function ($container) {
                return function (RequestInterface $request, ResponseInterface $response, \Exception $exception) {
                    return \BO\Zmsadmin\Helper\TwigExceptionHandler::withHtml($request, $response, $exception);
                };
            });
        $this->assertContains('Exception', (string)$response->getBody());
    }
}
