<?php

namespace BO\Zmsbackend\Tests;

class RoutingTest extends Base
{
    #[\Override]
    public function testRendering()
    {
        $this->assertEmpty(\BO\Slim\Bootstrap::loadRouting(\App::APP_PATH . '/routing.php'));
    }
}
