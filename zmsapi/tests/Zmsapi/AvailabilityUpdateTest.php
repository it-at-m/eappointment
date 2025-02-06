<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsentities\Availability as Entity;
use BO\Zmsdb\Availability as Query;
use BO\Zmsapi\Exception\Availability\AvailabilityUpdateFailed;

class AvailabilityUpdateTest extends Base
{
    protected $classname = "AvailabilityUpdate";

    public function testRendering()
    {
        $input = (new Entity)->createExample();
        $currentTimestamp = time();
        $input['startDate'] = $currentTimestamp + (2 * 24 * 60 * 60); // 2 days in the future
        $input['endDate'] = $currentTimestamp + (5 * 24 * 60 * 60);   // 5 days in the future
        $input['startTime'] = "09:00:00";
        $input['endTime'] = "17:00:00";
        $input['scope'] = [
            "id" => 312,
            "dayoff" => [
                [
                    "id" => 35,
                    "date" => $currentTimestamp + (7 * 24 * 60 * 60), // 7 days in the future
                    "name" => "1. Mai",
                    "lastChange" => $currentTimestamp
                ],
                [
                    "id" => 36,
                    "date" => $currentTimestamp + (14 * 24 * 60 * 60), // 14 days in the future
                    "name" => "Christi Himmelfahrt",
                    "lastChange" => $currentTimestamp
                ]
            ]
        ];
        $input['kind'] = "default";

        $entity = (new Query())->writeEntity($input);
        $this->setWorkstation();

        $response = $this->render([
            "id" => $entity->getId()
        ], [
            '__body' => json_encode([
                'availabilityList' => [
                    [
                        "id" => $entity->getId(),
                        "description" => "Test Öffnungszeit update",
                        "startDate" => $currentTimestamp + (2 * 24 * 60 * 60),
                        "endDate" => $currentTimestamp + (5 * 24 * 60 * 60),
                        "startTime" => "09:00:00",
                        "endTime" => "17:00:00",
                        "kind" => "default",
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
                'selectedDate' => date('Y-m-d')
            ])
        ], []);

        $this->assertStringContainsString('availability.json', (string) $response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testOverlappingAvailability()
    {
        $this->setWorkstation();
        $this->expectException(AvailabilityUpdateFailed::class);
    
        $startDate = strtotime('2025-03-04'); // A Tuesday
        $weekday = 4; // Tuesday
        $dayoffData = [
            [
                "id" => "302",
                "date" => 1458860400,
                "lastChange" => 1566566540,
                "name" => "Karfreitag"
            ]
        ];
    
        // Create first entity
        $input = (new Entity)->createExample();
        $input['startDate'] = $startDate;
        $input['endDate'] = $startDate;
        $input['startTime'] = "14:00:00";
        $input['endTime'] = "17:40:00";
        $input['weekday'] = array_combine(
            ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'],
            array_map(function($i) use ($weekday) { return $i === $weekday ? '4' : '0'; }, range(1, 7))
        );
        $input['bookable'] = [
            'startInDays' => 0,
            'endInDays' => 90
        ];
        $input['scope'] = [
            "id" => 392,
            "dayoff" => $dayoffData
        ];
        $input['kind'] = "default";
        $input['slotTimeInMinutes'] = 5;
        $input['workstationCount'] = [
            'public' => 6,
            'callcenter' => 6,
            'intern' => 0
        ];
        $entity = (new Query())->writeEntity($input);
    
        // Create second entity with overlapping time
        $secondInput = $input;
        $secondInput['startTime'] = "15:00:00";
        $secondInput['endTime'] = "17:40:00";
        $secondEntity = (new Query())->writeEntity($secondInput);
    
        $this->render([], [
            '__body' => json_encode([
                'availabilityList' => [
                    [
                        "id" => $entity->getId(),
                        "description" => "Overlapping Entry 1",
                        "startDate" => $startDate,
                        "endDate" => $startDate,
                        "startTime" => "14:00:00",
                        "endTime" => "17:40:00",
                        "kind" => "default",
                        "weekday" => array_combine(
                            ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'],
                            array_map(function($i) use ($weekday) { return $i === $weekday ? '4' : '0'; }, range(1, 7))
                        ),
                        "bookable" => [
                            'startInDays' => 0,
                            'endInDays' => 90
                        ],
                        "scope" => [
                            "id" => 392,
                            "dayoff" => $dayoffData
                        ],
                        "slotTimeInMinutes" => 5,
                        "workstationCount" => [
                            'public' => 6,
                            'callcenter' => 6,
                            'intern' => 0
                        ]
                    ],
                    [
                        "id" => $secondEntity->getId(),
                        "description" => "Overlapping Entry 2",
                        "startDate" => $startDate,
                        "endDate" => $startDate,
                        "startTime" => "15:00:00",
                        "endTime" => "17:40:00",
                        "kind" => "default",
                        "weekday" => array_combine(
                            ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'],
                            array_map(function($i) use ($weekday) { return $i === $weekday ? '4' : '0'; }, range(1, 7))
                        ),
                        "bookable" => [
                            'startInDays' => 0,
                            'endInDays' => 90
                        ],
                        "scope" => [
                            "id" => 392,
                            "dayoff" => $dayoffData
                        ],
                        "slotTimeInMinutes" => 5,
                        "workstationCount" => [
                            'public' => 6,
                            'callcenter' => 6,
                            'intern' => 0
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
        $this->expectException(AvailabilityUpdateFailed::class);
    
        $startDate = strtotime('2025-03-04'); // A Tuesday
        $weekday = 4; // Tuesday
        $dayoffData = [
            [
                "id" => "302",
                "date" => 1458860400,
                "lastChange" => 1566566540,
                "name" => "Karfreitag"
            ]
        ];
    
        // Create first entity
        $input = (new Entity)->createExample();
        $input['startDate'] = $startDate;
        $input['endDate'] = $startDate;
        $input['startTime'] = "14:00:00";
        $input['endTime'] = "17:40:00";
        $input['weekday'] = array_combine(
            ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'],
            array_map(function($i) use ($weekday) { return $i === $weekday ? '4' : '0'; }, range(1, 7))
        );
        $input['bookable'] = [
            'startInDays' => 0,
            'endInDays' => 90
        ];
        $input['scope'] = [
            "id" => 392,
            "dayoff" => $dayoffData
        ];
        $input['kind'] = "default";
        $input['slotTimeInMinutes'] = 5;
        $input['workstationCount'] = [
            'public' => 6,
            'callcenter' => 6,
            'intern' => 0
        ];
        $entity = (new Query())->writeEntity($input);
    
        // Create second entity with exact same times
        $secondEntity = (new Query())->writeEntity($input);
    
        $this->render([], [
            '__body' => json_encode([
                'availabilityList' => [
                    [
                        "id" => $entity->getId(),
                        "description" => "Duplicate Entry 1",
                        "startDate" => $startDate,
                        "endDate" => $startDate,
                        "startTime" => "14:00:00",
                        "endTime" => "17:40:00",
                        "kind" => "default",
                        "weekday" => array_combine(
                            ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'],
                            array_map(function($i) use ($weekday) { return $i === $weekday ? '4' : '0'; }, range(1, 7))
                        ),
                        "bookable" => [
                            'startInDays' => 0,
                            'endInDays' => 90
                        ],
                        "scope" => [
                            "id" => 392,
                            "dayoff" => $dayoffData
                        ],
                        "slotTimeInMinutes" => 5,
                        "workstationCount" => [
                            'public' => 6,
                            'callcenter' => 6,
                            'intern' => 0
                        ]
                    ],
                    [
                        "id" => $secondEntity->getId(),
                        "description" => "Duplicate Entry 2",
                        "startDate" => $startDate,
                        "endDate" => $startDate,
                        "startTime" => "14:00:00",
                        "endTime" => "17:40:00",
                        "kind" => "default",
                        "weekday" => array_combine(
                            ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'],
                            array_map(function($i) use ($weekday) { return $i === $weekday ? '4' : '0'; }, range(1, 7))
                        ),
                        "bookable" => [
                            'startInDays' => 0,
                            'endInDays' => 90
                        ],
                        "scope" => [
                            "id" => 392,
                            "dayoff" => $dayoffData
                        ],
                        "slotTimeInMinutes" => 5,
                        "workstationCount" => [
                            'public' => 6,
                            'callcenter' => 6,
                            'intern' => 0
                        ]
                    ]
                ],
                'selectedDate' => date('Y-m-d', $startDate)
            ])
        ], []);
    }

    public function testInvalidEndTime()
    {

        $input = (new Entity)->createExample();
        $currentTimestamp = time();
        $input['startDate'] = $currentTimestamp + (20 * 24 * 60 * 60); // 2 days in the future
        $input['endDate'] = $currentTimestamp + (20 * 24 * 60 * 60);
        $input['startTime'] = "17:00:00";
        $input['endTime'] = "09:00:00";
        $input['scope'] = [
            "id" => 312,
            "dayoff" => [
                [
                    "id" => 35,
                    "date" => $currentTimestamp + (70 * 24 * 60 * 60), // 7 days in the future
                    "name" => "1. Mai",
                    "lastChange" => $currentTimestamp
                ],
                [
                    "id" => 36,
                    "date" => $currentTimestamp + (140 * 24 * 60 * 60), // 14 days in the future
                    "name" => "Christi Himmelfahrt",
                    "lastChange" => $currentTimestamp
                ]
            ]
        ];
        $input['kind'] = "default";

        $entity = (new Query())->writeEntity($input);
        $this->setWorkstation();
        $this->expectException(AvailabilityUpdateFailed::class);

        $this->render([], [
            '__body' => json_encode([
                'availabilityList' => [
                    [
                        "id" => $entity->getId(),
                        "description" => "End Time Before Start Time",
                        "startDate" => time() + (20 * 24 * 60 * 60),
                        "endDate" => time() + (20 * 24 * 60 * 60),
                        "startTime" => "17:00:00",
                        "endTime" => "09:00:00",
                        "kind" => "default",
                        "scope" => [
                            "id" => 312,
                            "dayoff" => [
                                [
                                    "id" => 35,
                                    "date" => $currentTimestamp + (70 * 24 * 60 * 60),
                                    "name" => "1. Mai",
                                    "lastChange" => $currentTimestamp
                                ],
                                [
                                    "id" => 36,
                                    "date" => $currentTimestamp + (140 * 24 * 60 * 60),
                                    "name" => "Christi Himmelfahrt",
                                    "lastChange" => $currentTimestamp
                                ]
                            ]
                        ]
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

    public function testNotFound()
    {
        $this->setWorkstation();
        $this->expectException('\BO\Zmsapi\Exception\Availability\AvailabilityNotFound');
        $this->expectExceptionCode(404);

        $this->render(
            ["id" => 1],
            [
                '__body' => json_encode([
                    'availabilityList' => [
                        [
                            "id" => 1,
                            "description" => "Test Öffnungszeit not found",
                            "scope" => ["id" => 312]
                        ]
                    ],
                    'selectedDate' => date('Y-m-d')
                ])
            ],
            []
        );
    }
}
