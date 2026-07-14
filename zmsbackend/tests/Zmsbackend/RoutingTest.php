<?php

namespace BO\Zmsbackend\Tests;

class RoutingTest extends Base
{
    public function testRendering()
    {
        $this->assertEmpty(\BO\Slim\Bootstrap::loadRouting(\App::APP_PATH . '/routing.php'));
    }
}
