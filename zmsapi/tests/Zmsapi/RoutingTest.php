<?php

namespace BO\Zmsapi\Tests;

use \BO\Slim\Render;

class RoutingTest extends Base
{

    public function testRendering()
    {
        $this->assertEmpty(\BO\Slim\Bootstrap::loadRouting(\App::APP_PATH . '/routing.php'));
    }
}
