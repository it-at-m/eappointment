<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Exception\Availability\AvailabilityUpdateFailed;
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

        $this->assertStringContainsString('availability.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }
    public function testOverlappingAvailability()
    {
        $this->setWorkstation();
        $this->expectException(AvailabilityUpdateFailed::class);

        $this->render([], [
            '__body' => json_encode([
                'availabilityList' => [
                    [
                        "id" => 21202,
                        "description" => "Overlapping Entry 1",
                        "startDate" => time() + (2 * 24 * 60 * 60),
                        "endDate" => time() + (3 * 24 * 60 * 60),
                        "startTime" => "09:00:00",
                        "endTime" => "17:00:00",
                        "kind" => "default",
                        "scope" => ["id" => 312]
                    ],
                    [
                        "id" => 21203,
                        "description" => "Overlapping Entry 2",
                        "startDate" => time() + (2 * 24 * 60 * 60),
                        "endDate" => time() + (3 * 24 * 60 * 60),
                        "startTime" => "10:00:00",
                        "endTime" => "18:00:00",
                        "kind" => "default",
                        "scope" => ["id" => 312]
                    ]
                ],
                'selectedDate' => date('Y-m-d')
            ])
        ], []);
    }
    public function testDuplicateAvailability()
    {
        $this->setWorkstation();
        $this->expectException(AvailabilityUpdateFailed::class);

        $this->render([], [
            '__body' => json_encode([
                'availabilityList' => [
                    [
                        "id" => 21202,
                        "description" => "Duplicate Entry 1",
                        "startDate" => time() + (3 * 24 * 60 * 60),
                        "endDate" => time() + (4 * 24 * 60 * 60),
                        "startTime" => "09:00:00",
                        "endTime" => "17:00:00",
                        "kind" => "default",
                        "scope" => ["id" => 312]
                    ],
                    [
                        "id" => 21203,
                        "description" => "Duplicate Entry 2",
                        "startDate" => time() + (3 * 24 * 60 * 60),
                        "endDate" => time() + (4 * 24 * 60 * 60),
                        "startTime" => "09:00:00",
                        "endTime" => "17:00:00",
                        "kind" => "default",
                        "scope" => ["id" => 312]
                    ]
                ],
                'selectedDate' => date('Y-m-d')
            ])
        ], []);
    }

    public function testInvalidStartTime()
    {
        $this->setWorkstation();
        $this->expectException(AvailabilityUpdateFailed::class);

        $this->render([], [
            '__body' => json_encode([
                'availabilityList' => [
                    [
                        "description" => "Start Time in Future",
                        "startDate" => time() + (10 * 24 * 60 * 60), // 10 days in the future
                        "endDate" => time() + (15 * 24 * 60 * 60),
                        "startTime" => "09:00:00",
                        "endTime" => "17:00:00",
                        "kind" => "past",
                        "scope" => ["id" => 312]
                    ]
                ],
                'selectedDate' => date('Y-m-d')
            ])
        ], []);
    }

    public function testInvalidEndTime()
    {
        $this->setWorkstation();
        $this->expectException(AvailabilityUpdateFailed::class);

        $this->render([], [
            '__body' => json_encode([
                'availabilityList' => [
                    [
                        "description" => "End Time Before Start Time",
                        "startDate" => time() + (2 * 24 * 60 * 60),
                        "endDate" => time() + (2 * 24 * 60 * 60),
                        "startTime" => "17:00:00",
                        "endTime" => "09:00:00",
                        "kind" => "default",
                        "scope" => ["id" => 312]
                    ]
                ],
                'selectedDate' => date('Y-m-d')
            ])
        ], []);
    }

    public function testMissingKind()
    {
        $this->setWorkstation();
        $this->expectException(AvailabilityUpdateFailed::class);

        $this->render([], [
            '__body' => json_encode([
                'availabilityList' => [
                    [
                        "description" => "Missing Kind",
                        "startDate" => time() + (2 * 24 * 60 * 60),
                        "endDate" => time() + (3 * 24 * 60 * 60),
                        "startTime" => "09:00:00",
                        "endTime" => "17:00:00",
                        "scope" => ["id" => 312]
                    ]
                ],
                'selectedDate' => date('Y-m-d')
            ])
        ], []);
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
