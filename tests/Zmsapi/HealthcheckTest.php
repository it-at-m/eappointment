<?php

namespace BO\Zmsapi\Tests;

class HealthcheckTest extends Base
{
    protected $classname = "Healthcheck";

    public function testRendering()
    {
        $response = $this->render([ ], [ ], [ ]);
        $this->assertContains('WARN', (string)$response->getBody());
    }
}
