<?php

namespace BO\Zmsadmin\Tests;

class AvailabilityUpdateTest extends Base
{
    protected $arguments = [];

    protected $parameters = [
        '__body' => '{
            "id": "68985",
            "weekday": {
                "sunday": "0",
                "monday": "0",
                "tuesday": "0",
                "wednesday": "8",
                "thursday": "0",
                "friday": "0",
                "saturday": "0"
            },
            "repeat": {
                "afterWeeks": "1",
                "weekOfMonth": "0"
            },
            "bookable": {
                "startInDays": "0",
                "endInDays": "60"
            },
            "workstationCount": {
                "public": "3",
                "callcenter": "3",
                "intern": "3"
            },
            "multipleSlotsAllowed": "0",
            "slotTimeInMinutes": "10",
            "startDate": 1453935600,
            "endDate": 1463868000,
            "startTime": "08:00:00",
            "endTime": "12:50:00",
            "type": "appointment",
            "description": "",
            "scope": {
                "id": "141"
            }
        }'
    ];

    protected $classname = "AvailabilityUpdateList";

    public function testRendering()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readPostResult',
                    'url' => '/availability/',
                    'response' => $this->readFixture("GET_availability_68985.json")
                ]
            ]
        );
        $response = $this->render([], $this->parameters, [], 'POST');
        $this->assertStringContainsString('"id":"68985"', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testEmpty()
    {
        $this->expectException('\BO\Mellon\Failure\Exception');
        $response = $this->render([], [
            '__body'=> ''
        ], [], 'POST');
        $this->assertEquals(200, $response->getStatusCode());
    }
}
