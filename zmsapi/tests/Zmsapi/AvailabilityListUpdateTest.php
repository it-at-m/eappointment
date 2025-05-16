<?php

namespace BO\Zmsapi\Tests;

class AvailabilityListUpdateTest extends Base
{
    protected $classname = "AvailabilityListUpdate";

    public function testRendering()
    {
        $this->setWorkstation();
        $response = $this->render([], [
            '__body' => '{
                "availabilityList": [
                    {
                        "id": 21202,
                        "description": "Test Öffnungszeit update",
                        "scope": {
                            "id": 312
                        },
                        "weekday": {
                            "monday": 1,
                            "tuesday": 0,
                            "wednesday": 0,
                            "thursday": 0,
                            "friday": 0,
                            "saturday": 0,
                            "sunday": 0
                        },
                        "startDate": ' . strtotime("+1 day") . ',
                        "endDate": ' . strtotime("+30 days") . ',
                        "startTime": "09:00:00",
                        "endTime": "17:00:00",
                        "slotTimeInMinutes": 60,
                        "workstationCount": {
                            "public": 1,
                            "callcenter": 0,
                            "intern": 0
                        }
                    },
                    {
                        "description": "Test Öffnungszeit ohne id",
                        "scope": {
                            "id": 141
                        },
                        "weekday": {
                            "monday": 1,
                            "tuesday": 0,
                            "wednesday": 0,
                            "thursday": 0,
                            "friday": 0,
                            "saturday": 0,
                            "sunday": 0
                        },
                        "startDate": ' . strtotime("+1 day") . ',
                        "endDate": ' . strtotime("+30 days") . ',
                        "startTime": "09:00:00",
                        "endTime": "17:00:00",
                        "slotTimeInMinutes": 60,
                        "workstationCount": {
                            "public": 1,
                            "callcenter": 0,
                            "intern": 0
                        }
                    }
                ],
                "selectedDate": "' . date("Y-m-d", strtotime("+1 day")) . '"
            }'
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

    public function testUpdateFailed()
    {
        $this->setWorkstation();
        $this->expectException('\BO\Zmsapi\Exception\Availability\AvailabilityListUpdateFailed');
        $this->expectExceptionCode(400);
        $this->render([], [
            '__body' => '{
                "availabilityList": [
                    {
                        "id": 99999,
                        "description": "Test Öffnungszeit update failed",
                        "scope": {
                            "id": 312
                        },
                        "weekday": {
                            "monday": 1,
                            "tuesday": 0,
                            "wednesday": 0,
                            "thursday": 0,
                            "friday": 0,
                            "saturday": 0,
                            "sunday": 0
                        },
                        "startDate": ' . strtotime("+1 day") . ',
                        "endDate": ' . strtotime("+30 days") . ',
                        "startTime": "09:00:00",
                        "endTime": "17:00:00",
                        "slotTimeInMinutes": 60,
                        "workstationCount": {
                            "public": 1,
                            "callcenter": 0,
                            "intern": 0
                        }
                    }
                ],
                "selectedDate": "' . date("Y-m-d", strtotime("+1 day")) . '"
            }',
            'migrationfix' => 0
        ], []);
    }
}
