<?php

namespace BO\Zmsapi\Tests;

class AvailabilityGetTest extends Base
{
    protected $classname = "AvailabilityGet";

    public function testRendering()
    {
        $this->setWorkstation();
        $response = $this->render(['id' => 21202], [], []);
        $this->assertContains('availability.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testEmpty()
    {
        $this->setWorkstation();
        $this->expectException('\ErrorException');
        $this->render([], [], []);
    }

    public function testNotFound()
    {
        $this->setWorkstation();
        $this->expectException('\BO\Zmsapi\Exception\Availability\AvailabilityNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 1], [], []);
    }
}
