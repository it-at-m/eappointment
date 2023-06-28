<?php

namespace BO\Zmsapi\Tests;

class AvailabilitySlotsUpdateTest extends Base
{
    protected $classname = "AvailabilitySlotsUpdate";

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
                }
            ]'
        ], []);
        $this->assertStringContainsString('availability.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testEmpty()
    {
        $this->setWorkstation();
        $this->expectException('\BO\Mellon\Failure\Exception');
        $this->render([], [], []);
    }

    public function testEmptyBody()
    {
        $this->setWorkstation();
        $this->expectException('\BO\Zmsapi\Exception\BadRequest');
        $this->expectExceptionCode(400);
        $this->render([], [
            '__body' => '[]'
        ], []);
    }

    public function testNotFound()
    {
        $this->setWorkstation();
        $this->expectException('\BO\Zmsapi\Exception\Availability\AvailabilityNotFound');
        $this->expectExceptionCode(404);
        $this->render([], [
            '__body' => '[
                {
                  "id": 99999,
                  "description": "Test Öffnungszeit not found",
                  "scope": {
                      "id": 312
                  }
                }
            ]',
            'migrationfix' => 0
        ], []);
    }
}
