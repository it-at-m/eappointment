<?php

namespace BO\Zmsapi\Tests;

class AvailabilityAddTest extends Base
{
    protected $classname = "AvailabilityAdd";

    public function testRendering()
    {
        $this->setWorkstation();
        $response = $this->render([], [
            '__body' => '[
                {
                      "id": 21202,
                      "description": "Test Öffnungszeit update",
                      "scope": {
                          "id": 312
                      }
                  },
                  {
                    "id": 1,
                    "description": "Test Öffnungszeit neu mit id",
                    "scope": {
                        "id": 141
                    }
                },
                {
                    "description": "Test Öffnungszeit ohne id",
                    "scope": {
                        "id": 141
                    }
                }
            ]'
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
        $this->render([], [
            '__body' => '[
                
            ]'
        ], []);
    }
}
