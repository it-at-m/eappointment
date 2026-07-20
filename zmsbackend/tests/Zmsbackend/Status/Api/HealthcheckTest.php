<?php

namespace BO\Zmsbackend\Tests\Status\Api;

class HealthcheckTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "Healthcheck";

    public function testRendering()
    {
        $response = $this->render([ ], [ ], [ ]);
        $this->assertStringContainsString('WARN', (string)$response->getBody());
    }
}
