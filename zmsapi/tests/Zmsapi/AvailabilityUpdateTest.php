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
        $startDate = $currentTimestamp + (2 * 24 * 60 * 60); // 2 days in the future
        $weekday = strtolower(date('l', $startDate));
        $weekdayBitmap = [
            'sunday' => $weekday === 'sunday' ? 1 : 0,
            'monday' => $weekday === 'monday' ? 2 : 0,
            'tuesday' => $weekday === 'tuesday' ? 4 : 0,
            'wednesday' => $weekday === 'wednesday' ? 8 : 0,
            'thursday' => $weekday === 'thursday' ? 16 : 0,
            'friday' => $weekday === 'friday' ? 32 : 0,
            'saturday' => $weekday === 'saturday' ? 64 : 0
        ];
        
        $input['startDate'] = $startDate;
        $input['endDate'] = $startDate + (3 * 24 * 60 * 60);
        $input['startTime'] = "09:00:00";
        $input['endTime'] = "17:00:00";
        $input['weekday'] = $weekdayBitmap;
        $input['scope'] = [
            "id" => 312,
            "provider" => [
                "id" => 123456,
                "name" => "",
                "source" => "dldb"
            ],
            "shortName" => "Test",
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
                        "startDate" => $startDate,
                        "endDate" => $startDate + (3 * 24 * 60 * 60),
                        "startTime" => "09:00:00",
                        "endTime" => "17:00:00",
                        "kind" => "default",
                        "weekday" => $weekdayBitmap,
                        "scope" => [
                            "id" => 312,
                            "provider" => [
                                "id" => 123456,
                                "name" => "",
                                "source" => "dldb"
                            ],
                            "shortName" => "Test",
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
            "provider" => [
                "id" => 123456,
                "name" => "",
                "source" => "dldb"
            ],
            "shortName" => "Test",
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
                            "provider" => [
                                "id" => 123456,
                                "name" => "",
                                "source" => "dldb"
                            ],
                            "shortName" => "Test",
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
