<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Exception\Availability\AvailabilityAddFailed;
use \DateTime;
class AvailabilityAddTest extends Base
{
    protected $classname = "AvailabilityAdd";

    public function testRendering()
    {
        $this->setWorkstation();
        $startDate = time() + (2 * 24 * 60 * 60); // 2 days in the future
        $weekday = strtolower(date('l', $startDate));
        $currentTimestamp = time();
        
        $response = $this->render([], [
            '__body' => json_encode([
                'availabilityList' => [
                    [
                        "id" => 21202,
                        "description" => "Test Öffnungszeit update",
                        "startDate" => $startDate,
                        "endDate" => $startDate + (3 * 24 * 60 * 60),
                        "startTime" => "09:00:00",
                        "endTime" => "17:00:00",
                        "kind" => "default",
                        "weekday" => array_combine(
                            ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'],
                            array_map(function($day) use ($weekday) { 
                                return $day === $weekday ? '4' : '0'; 
                            }, ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'])
                        ),
                        "scope" => [
                            "id" => 312,
                            "dayoff" => [
                                [
                                    "id" => 35,
                                    "date" => $currentTimestamp + (7 * 24 * 60 * 60),
                                    "name" => "1. Mai",
                                    "lastChange" => $currentTimestamp
                                ],
                                [
                                    "id" => 36,
                                    "date" => $currentTimestamp + (14 * 24 * 60 * 60),
                                    "name" => "Christi Himmelfahrt",
                                    "lastChange" => $currentTimestamp
                                ]
                            ]
                        ]
                    ]
                ],
                'selectedDate' => date('Y-m-d', $startDate)
            ])
        ], []);
    
        $this->assertStringContainsString('availability.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testOverlappingAvailability()
    {
        $this->setWorkstation();
        $this->expectException(AvailabilityAddFailed::class);
    
        $startDate = time() + (2 * 24 * 60 * 60);
        $weekday = (int)date('N', $startDate);
        $dayoffData = [
            [
                "id" => "302",
                "date" => 1458860400,
                "lastChange" => 1566566540,
                "name" => "Karfreitag"
            ]
        ];
    
        $this->render([], [
            '__body' => json_encode([
                'availabilityList' => [
                    [
                        "id" => 21202,
                        "description" => "Overlapping Entry 1",
                        "startDate" => $startDate,
                        "endDate" => $startDate + (24 * 60 * 60),
                        "startTime" => "09:00:00",
                        "endTime" => "17:00:00",
                        "kind" => "default",
                        "weekday" => array_combine(
                            ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'],
                            array_map(function($i) use ($weekday) { return $i === $weekday ? '4' : '0'; }, range(1, 7))
                        ),
                        "scope" => [
                            "id" => 312,
                            "dayoff" => $dayoffData
                        ]
                    ],
                    [
                        "id" => 21203,
                        "description" => "Overlapping Entry 2",
                        "startDate" => $startDate,
                        "endDate" => $startDate + (24 * 60 * 60),
                        "startTime" => "10:00:00",
                        "endTime" => "18:00:00",
                        "kind" => "default",
                        "weekday" => array_combine(
                            ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'],
                            array_map(function($i) use ($weekday) { return $i === $weekday ? '4' : '0'; }, range(1, 7))
                        ),
                        "scope" => [
                            "id" => 312,
                            "dayoff" => $dayoffData
                        ]
                    ]
                ],
                'selectedDate' => date('Y-m-d', $startDate)
            ])
        ], []);
    }
    
    public function testDuplicateOverlappingAvailability()
    {
        $this->setWorkstation();
        $this->expectException(AvailabilityAddFailed::class);
    
        $startDate = time() + (3 * 24 * 60 * 60);
        $weekday = (int)date('N', $startDate);
        $dayoffData = [
            [
                "id" => "302",
                "date" => 1458860400,
                "lastChange" => 1566566540,
                "name" => "Karfreitag"
            ]
        ];
    
        $this->render([], [
            '__body' => json_encode([
                'availabilityList' => [
                    [
                        "id" => 21202,
                        "description" => "Duplicate Entry 1",
                        "startDate" => $startDate,
                        "endDate" => $startDate + (24 * 60 * 60),
                        "startTime" => "09:00:00",
                        "endTime" => "17:00:00",
                        "kind" => "default",
                        "weekday" => array_combine(
                            ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'],
                            array_map(function($i) use ($weekday) { return $i === $weekday ? '4' : '0'; }, range(1, 7))
                        ),
                        "scope" => [
                            "id" => 312,
                            "dayoff" => $dayoffData
                        ]
                    ],
                    [
                        "id" => 21203,
                        "description" => "Duplicate Entry 2",
                        "startDate" => $startDate,
                        "endDate" => $startDate + (24 * 60 * 60),
                        "startTime" => "09:00:00",
                        "endTime" => "17:00:00",
                        "kind" => "default",
                        "weekday" => array_combine(
                            ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'],
                            array_map(function($i) use ($weekday) { return $i === $weekday ? '4' : '0'; }, range(1, 7))
                        ),
                        "scope" => [
                            "id" => 312,
                            "dayoff" => $dayoffData
                        ]
                    ]
                ],
                'selectedDate' => date('Y-m-d', $startDate)
            ])
        ], []);
    }

    public function testInvalidEndTime()
    {
        $this->setWorkstation();
        $this->expectException(AvailabilityAddFailed::class);

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

    public function testAddFailed()
    {
        $this->setWorkstation();
        $this->expectException('\BO\Zmsapi\Exception\Availability\AvailabilityAddFailed');
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
