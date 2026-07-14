<?php

namespace BO\Zmsbackend\Tests\Status\Api;

class StatusGetTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "StatusGet";

    public function testRendering()
    {
        $response = $this->render();
        $this->assertStringContainsString('status.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }
}
