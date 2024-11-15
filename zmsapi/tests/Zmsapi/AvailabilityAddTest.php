<?php

namespace BO\Zmsapi\Tests;

class AvailabilityAddTest extends Base
{
    protected $classname = "AvailabilityAdd";


    public function testRendering()
    {
        $this->setWorkstation();

        $response = $this->render([], [
            '__body' => json_encode([
                'availabilityList' => [
                    [
                        "id" => 21202,
                        "description" => "Test Öffnungszeit update",
                        "startDate" => time() + (2 * 24 * 60 * 60), // 2 days in the future
                        "endDate" => time() + (5 * 24 * 60 * 60),   // 5 days in the future
                        "startTime" => "09:00:00",
                        "endTime" => "17:00:00",
                        "kind" => "default",
                        "scope" => [
                            "id" => 312
                        ]
                    ],
                    [
                        "description" => "Test Öffnungszeit ohne id",
                        "startDate" => time() + (1 * 24 * 60 * 60), // 1 day in the future
                        "endDate" => time() + (4 * 24 * 60 * 60),   // 4 days in the future
                        "startTime" => "10:00:00",
                        "endTime" => "16:30:00",
                        "kind" => "default",
                        "scope" => [
                            "id" => 141
                        ]
                    ]
                ],
                'selectedDate' => date('Y-m-d')
            ])
        ], []);

        error_log(json_encode((string)$response->getBody()));
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
        $this->expectException('\BO\Zmsapi\Exception\Availability\AvailabilityUpdateFailed');
        $this->expectExceptionCode(400);

        $this->render([], [
            '__body' => json_encode([
                'availabilityList' => [
                    [
                        "id" => 99999,
                        "description" => "Test Öffnungszeit update failed",
                        "scope" => ["id" => 312]
                    ]
                ],
                'selectedDate' => date('Y-m-d')
            ]),
            'migrationfix' => 0
        ], []);
    }
}
