<?php

namespace BO\Zmsapi\Tests;

class AvailabilitySlotsUpdateTest extends Base
{
    protected $classname = "AvailabilitySlotsUpdate";

    public function testRendering()
    {
        $workstation = $this->setWorkstation();
        $workstation->getUseraccount()->permissions['availability'] = true;
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
        $workstation = $this->setWorkstation();
        $workstation->getUseraccount()->permissions['availability'] = true;
        $this->expectException('\BO\Mellon\Failure\Exception');
        $this->render([], [], []);
    }

    public function testEmptyBody()
    {
        $workstation = $this->setWorkstation();
        $workstation->getUseraccount()->permissions['availability'] = true;
        $this->expectException('\BO\Zmsapi\Exception\BadRequest');
        $this->expectExceptionCode(400);
        $this->render([], [
            '__body' => '[]'
        ], []);
    }

    public function testNotFound()
    {
        $workstation = $this->setWorkstation();
        $workstation->getUseraccount()->permissions['availability'] = true;
        $this->expectException('\BO\Zmsapi\Exception\Availability\AvailabilityNotFound');
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

    public function testMissingAvailabilityPermissionThrows403()
    {
        $this->setWorkstation();
        $this->expectException('\BO\Zmsentities\Exception\UserAccountMissingPermissions');
        $this->render([], [
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
    }
}
