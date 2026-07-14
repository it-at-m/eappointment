<?php

namespace BO\Zmsbackend\Tests\Process\Api;

use BO\Zmsbackend\Helper\User;

class ProcessReservedListTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "ProcessReservedList";

    public function testRendering()
    {
        $this->setWorkstation();
        $response = $this->render([], [], []);
        $this->assertStringContainsString('process.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }
}
