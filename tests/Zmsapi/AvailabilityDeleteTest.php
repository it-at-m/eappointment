<?php

namespace BO\Zmsapi\Tests;

class AvailabilityDeleteTest extends Base
{
    protected $classname = "AvailabilityDelete";

    public function testRendering()
    {
        $this->setWorkstation();
        $response = $this->render(['id' => 21202], [], []); //Test Availability
        $this->assertContains('startDate', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testNotFound()
    {
        $this->setWorkstation();
        $response = $this->render(['id' => 1], [], []);
        $this->assertContains('Not found', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }
}
