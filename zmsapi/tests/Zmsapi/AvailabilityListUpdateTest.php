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

    public function testWeekdayValidation()
    {
        $this->setWorkstation();
        $response = $this->render([], [
            '__body' => '{
                "availabilityList": [
                    {
                        "description": "Test invalid weekday configuration",
                        "scope": {
                            "id": 141
                        },
                        "weekday": {
                            "monday": 0,
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
        $this->assertEquals(400, $response->getStatusCode());
        $responseData = json_decode((string)$response->getBody(), true);
        $this->assertArrayHasKey('meta', $responseData);
        $this->assertTrue($responseData['meta']['error']);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('errors', $responseData['data']);
        $this->assertNotEmpty($responseData['data']['errors']);
        $this->assertEquals('weekdayRequired', $responseData['data']['errors'][0]['type']);
        $this->assertEquals('Mindestens ein Wochentag muss ausgewählt sein.', $responseData['data']['errors'][0]['message']);
    }

    public function testStartTimeValidation()
    {
        $this->setWorkstation();
        $response = $this->render([], [
            '__body' => '{
                "availabilityList": [
                    {
                        "description": "Test invalid start time",
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
                        "startTime": "17:00:00",
                        "endTime": "09:00:00",
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
        $this->assertEquals(400, $response->getStatusCode());
        $responseData = json_decode((string)$response->getBody(), true);
        $this->assertArrayHasKey('meta', $responseData);
        $this->assertTrue($responseData['meta']['error']);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('errors', $responseData['data']);
        $this->assertNotEmpty($responseData['data']['errors']);
        $this->assertEquals('endTime', $responseData['data']['errors'][0]['type']);
        $this->assertEquals('Die Endzeit darf nicht vor der Startzeit liegen.', $responseData['data']['errors'][0]['message']);
    }

    public function testSlotTimeValidation()
    {
        $this->setWorkstation();
        $this->expectException('\BO\Zmsentities\Exception\SchemaValidation');
        $this->render([], [
            '__body' => '{
                "availabilityList": [
                    {
                        "description": "Test invalid slot time",
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
                        "slotTimeInMinutes": "invalid",
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
    }

    public function testBookableDayRangeValidation()
    {
        $this->setWorkstation();
        $response = $this->render([], [
            '__body' => '{
                "availabilityList": [
                    {
                        "description": "Test invalid bookable day range",
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
                        "bookable": {
                            "startInDays": 31,
                            "endInDays": 30
                        },
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
        $this->assertEquals(400, $response->getStatusCode());
        $responseData = json_decode((string)$response->getBody(), true);
        $this->assertArrayHasKey('meta', $responseData);
        $this->assertTrue($responseData['meta']['error']);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('errors', $responseData['data']);
        $this->assertNotEmpty($responseData['data']['errors']);
        $this->assertEquals('bookableDayRange', $responseData['data']['errors'][0]['type']);
        $this->assertEquals('Bitte geben Sie im Feld \'von\' eine kleinere Zahl ein als im Feld \'bis\', wenn Sie bei \'Buchbar\' sind.', $responseData['data']['errors'][0]['message']);
    }

    public function testTypeValidation()
    {
        $this->setWorkstation();
        $this->expectException('\BO\Zmsentities\Exception\SchemaValidation');
        $this->render([], [
            '__body' => '{
                "availabilityList": [
                    {
                        "description": "Test invalid type",
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
                        "type": "invalid_type",
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
    }
}
