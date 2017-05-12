<?php

namespace BO\Zmsapi\Tests;

class AvailabilityUpdateTest extends Base
{
    protected $classname = "AvailabilityUpdate";

    public function testRendering()
    {
        $this->setWorkstation();
        $response = $this->render(["id"=> 21202], [
            '__body' => '{
                  "id": 21202,
                  "description": "",
                  "scope": {
                      "id": 312
                  }
              }'
        ], []);
        $this->assertContains('availability.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testEmpty()
    {
        $this->setWorkstation();
        $this->setExpectedException('\BO\Mellon\Failure\Exception');
        $this->render([], [], []);
    }

    public function testNotFound()
    {
        $this->setWorkstation();
        $this->expectException('\BO\Zmsapi\Exception\Availability\AvailabilityNotFound');
        $this->expectExceptionCode(404);
        $this->render(["id"=> 1], [
            '__body' => '{
                  "id": 1,
                  "description": "Test Öffnungszeit not found",
                  "scope": {
                      "id": 312
                  }
              }'
        ], []);
    }
}
