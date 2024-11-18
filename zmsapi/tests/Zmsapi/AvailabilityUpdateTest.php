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

    public function testDuplicateOverlappingAvailability()
    {

        $input = (new Entity)->createExample();
        $currentTimestamp = time();
        $input['startDate'] = $currentTimestamp + (20 * 24 * 60 * 60); // 2 days in the future
        $input['endDate'] = $currentTimestamp + (50 * 24 * 60 * 60);   // 5 days in the future
        $input['startTime'] = "09:00:00";
        $input['endTime'] = "17:00:00";
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
        $secondEntity = (new Query())->writeEntity($input);
        $this->setWorkstation();
        $currentTimestamp = time();
        $this->expectException(AvailabilityUpdateFailed::class);

        $this->render([], [
            '__body' => json_encode([
                'availabilityList' => [
                    [
                        "id" => $entity->getId(),
                        "description" => "Duplicate Entry 1",
                        "startDate" => $currentTimestamp + (20 * 24 * 60 * 60),
                        "endDate" => $currentTimestamp + (20 * 24 * 60 * 60),
                        "startTime" => "09:00:00",
                        "endTime" => "17:00:00",
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
                        ],

                    ],
                    [
                        "id" => $secondEntity->getId(),
                        "description" => "Duplicate Entry 2",
                        "startDate" => $currentTimestamp + (20 * 24 * 60 * 60),
                        "endDate" => $currentTimestamp + (20 * 24 * 60 * 60),
                        "startTime" => "09:00:00",
                        "endTime" => "17:00:00",
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

    public function testOverlappingAvailability()
    {

        $input = (new Entity)->createExample();
        $currentTimestamp = time();
        $input['startDate'] = $currentTimestamp + (20 * 24 * 60 * 60); // 2 days in the future
        $input['endDate'] = $currentTimestamp + (50 * 24 * 60 * 60);   // 5 days in the future
        $input['startTime'] = "09:00:00";
        $input['endTime'] = "17:00:00";
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


        $secondInput = (new Entity)->createExample();
        $currentTimestamp = time();
        $secondInput['startDate'] = $currentTimestamp + (20 * 24 * 60 * 60); // 2 days in the future
        $secondInput['endDate'] = $currentTimestamp + (50 * 24 * 60 * 60);   // 5 days in the future
        $secondInput['startTime'] = "10:00:00";
        $secondInput['endTime'] = "20:00:00";
        $secondInput['scope'] = [
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
        $secondInput['kind'] = "default";

        $entity = (new Query())->writeEntity($input);
        $secondEntity = (new Query())->writeEntity($secondInput);
        $this->setWorkstation();
        $currentTimestamp = time();
        $this->expectException(AvailabilityUpdateFailed::class);

        $this->render([], [
            '__body' => json_encode([
                'availabilityList' => [
                    [
                        "id" => $entity->getId(),
                        "description" => "Duplicate Entry 1",
                        "startDate" => $currentTimestamp + (200 * 24 * 60 * 60),
                        "endDate" => $currentTimestamp + (200 * 24 * 60 * 60),
                        "startTime" => "09:00:00",
                        "endTime" => "17:00:00",
                        "kind" => "default",
                        "scope" => [
                            "id" => 312,
                            "dayoff" => [
                                [
                                    "id" => 35,
                                    "date" => $currentTimestamp + (700 * 24 * 60 * 60),
                                    "name" => "1. Mai",
                                    "lastChange" => $currentTimestamp
                                ],
                                [
                                    "id" => 36,
                                    "date" => $currentTimestamp + (1400 * 24 * 60 * 60),
                                    "name" => "Christi Himmelfahrt",
                                    "lastChange" => $currentTimestamp
                                ]
                            ]
                        ],

                    ],
                    [
                        "id" => $secondEntity->getId(), // Duplicate ID
                        "description" => "Duplicate Entry 2",
                        "startDate" => $currentTimestamp + (200 * 24 * 60 * 60),
                        "endDate" => $currentTimestamp + (200 * 24 * 60 * 60),
                        "startTime" => "10:00:00",
                        "endTime" => "20:00:00",
                        "kind" => "default",
                        "scope" => [
                            "id" => 312,
                            "dayoff" => [
                                [
                                    "id" => 35,
                                    "date" => $currentTimestamp + (700 * 24 * 60 * 60),
                                    "name" => "1. Mai",
                                    "lastChange" => $currentTimestamp
                                ],
                                [
                                    "id" => 36,
                                    "date" => $currentTimestamp + (1400 * 24 * 60 * 60),
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

    public function testInvalidEndTime()
    {

        $input = (new Entity)->createExample();
        $currentTimestamp = time();
        $input['startDate'] = $currentTimestamp + (20 * 24 * 60 * 60); // 2 days in the future
        $input['endDate'] = $currentTimestamp + (50 * 24 * 60 * 60);   // 5 days in the future
        $input['startTime'] = "09:00:00";
        $input['endTime'] = "17:00:00";
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
                                    "date" => $currentTimestamp + (700 * 24 * 60 * 60),
                                    "name" => "1. Mai",
                                    "lastChange" => $currentTimestamp
                                ],
                                [
                                    "id" => 36,
                                    "date" => $currentTimestamp + (1400 * 24 * 60 * 60),
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
