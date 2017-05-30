<?php

namespace BO\Zmsapi\Tests;

class StatusGetTest extends Base
{
    protected $classname = "StatusGet";

    public function testRendering()
    {
        $response = $this->render();
        $this->assertContains('status.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }
}
