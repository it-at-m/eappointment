<?php

namespace BO\Zmsbackend\Tests\Availability\Api;

class AvailabilitySlotsUpdateTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "AvailabilitySlotsUpdate";

    public function testRendering()
    {
        $this->setWorkstation() ->getUseraccount() ->setPermissions('availability');
        $response = $this->render([], [
            '__body' => '[
                {
                    "id": 21202,
                    "description": "Test Öffnungszeit update",
                    "scope": {
                        "id": 312,
                        "provider": {
                            "id": 123456,
                            "name": "Flughafen Schönefeld, Aufsicht",
                            "source": "dldb"
                        },
                        "shortName": "Zentrale"
                    }
                }
            ]'
        ], []);
        $this->assertStringContainsString('availability.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testEmpty()
    {
        $this->setWorkstation() ->getUseraccount() ->setPermissions('availability');
        $this->expectException('\BO\Mellon\Failure\Exception');
        $this->render([], [], []);
    }

    public function testEmptyBody()
    {
        $this->setWorkstation() ->getUseraccount() ->setPermissions('availability');
        $this->expectException('\BO\Zmsbackend\Exception\BadRequest');
        $this->expectExceptionCode(400);
        $this->render([], [
            '__body' => '[]'
        ], []);
    }

    public function testNotFound()
    {
        $this->setWorkstation() ->getUseraccount() ->setPermissions('availability');
        $this->expectException('\BO\Zmsbackend\Availability\Exception\AvailabilityNotFound');
        $this->expectExceptionCode(404);
        $this->render([], [
            '__body' => '[
                {
                    "id": 99999,
                    "description": "Test Öffnungszeit not found",
                    "scope": {
                        "id": 312,
                        "provider": {
                            "id": 123456,
                            "name": "Flughafen Schönefeld, Aufsicht",
                            "source": "dldb"
                        },
                        "shortName": "Zentrale"
                    }
                }
            ]',
            'migrationfix' => 0
        ], []);
    }
}
