<?php

namespace BO\Zmsapi\Tests;

class AvailabilityGetTest extends Base
{
    protected $classname = "AvailabilityGet";

    public function testRendering()
    {
        $response = $this->render([21202], [], []);
        $this->assertContains('availability.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testEmpty()
    {
        $this->setExpectedException('\ErrorException');
        $this->render([], [], []);
    }

    public function testNotFound()
    {
        $this->expectException('\BO\Zmsapi\Exception\Availability\AvailabilityNotFound');
        $this->expectExceptionCode(404);
        $this->render([1], [], []);
    }
}
