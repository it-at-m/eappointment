<?php

namespace BO\Zmsapi\Tests;

use \BO\Zmsentities\Availability as Entity;

use \BO\Zmsdb\Availability as Query;

class AvailabilityUpdateTest extends Base
{
    protected $classname = "AvailabilityUpdate";

    public function testRendering()
    {

        $input = (new Entity)->createExample();
        $input = [
            "description" => "Test Öffnungszeit update",
            "startDate" => time() + (2 * 24 * 60 * 60), // 2 days in the future
            "endDate" => time() + (5 * 24 * 60 * 60),   // 5 days in the future
            "startTime" => "09:00:00",
            "endTime" => "17:00:00",
            "kind" => "default",
            "scope" => ["id" => 312],
        ];

        // Write the entity to the database
        $entity = (new Query())->writeEntity($input);
        $this->setWorkstation();

        $response = $this->render([
            "id" => $entity->getId()
        ], [
            '__body' => json_encode([
                'availabilityList' => [$input],
                'selectedDate' => date('Y-m-d'),
                'dayoff' => [
                    [
                        "id" => 302,
                        "date" => time() + (10 * 24 * 60 * 60),
                        "name" => "Test Dayoff"
                    ]
                ]
            ])
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
