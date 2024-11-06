<?php

namespace BO\Zmscitizenapi\Tests;

class AvailableAppointmentsListTest extends Base
{
    protected $classname = "AvailableAppointmentsList";

    public function testRendering()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/free/',
                    'response' => $this->readFixture("GET_appointments.json")
                ]
            ]
        );
    
        $parameters = [
            'date' => '3000-09-21',
            'officeId' => '10546',
            'serviceId' => '1063423',
            'serviceCount' => '1',
        ];
    
        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'appointmentTimestamps' => [32526616522],
            'status' => 200,
        ];
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }
    

    public function testEmptyAppointments()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/free/',
                    'response' => $this->readFixture("GET_appointments_empty.json")
                ]
            ]
        );

        $parameters = [
            'date' => '3000-09-21',
            'officeId' => '10546',
            'serviceId' => '1063423',
            'serviceCount' => '1',
        ];

        $response = $this->render([], $parameters, []);
        $expectedResponse = [
            'errors' => [
                [
                    'appointmentTimestamps' => [],
                    'errorCode' => "appointmentNotAvailable",
                    'errorMessage' => 'Der von Ihnen gewählte Termin ist leider nicht mehr verfügbar.',                
                    'status' => 404,
                ]
            ],
            'status' => 404
        ];
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, json_decode((string)$response->getBody(), true));
    }

    public function testDateMissing()
    {
        $parameters = [
            'officeId' => '10546',
            'serviceId' => '1063423',
            'serviceCount' => '1',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                [
                    'errorMessage' => 'date is required and must be a valid date.',                
                    'status' => 400,
                ]
            ],
            'status' => 400
        ];
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testOfficeIdMissing()
    {
        $parameters = [
            'date' => '3000-09-21',
            'serviceId' => '1063423',
            'serviceCount' => '1',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                [
                    'errorMessage' => 'officeId should be a 32-bit integer.',                
                    'status' => 400,
                ]
            ],
            'status' => 400
        ];
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testServiceIdMissing()
    {
        $parameters = [
            'date' => '3000-09-21',
            'officeId' => '10546',
            'serviceCount' => '1',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                [
                    'errorMessage' => 'serviceId should be a comma-separated string of integers.',                
                    'status' => 400,
                ]
            ],
            'status' => 400
        ];
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testServiceCountMissing()
    {
        $parameters = [
            'date' => '3000-09-21',
            'officeId' => '10546',
            'serviceId' => '1063423',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                [
                    'errorMessage' => 'serviceCount should be a comma-separated string of integers.',                
                    'status' => 400,
                ]
            ],
            'status' => 400
        ];
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testDateAndOfficeIdMissing()
    {
        $parameters = [
            'serviceId' => '1063423',
            'serviceCount' => '1',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                [
                    'errorMessage' => 'date is required and must be a valid date.',                
                    'status' => 400,
                ],
                [
                    'errorMessage' => 'officeId should be a 32-bit integer.',                
                    'status' => 400,
                ]
            ],
            'status' => 400
        ];
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testDateAndServiceIdMissing()
    {
        $parameters = [
            'officeId' => '10546',
            'serviceCount' => '1',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                [
                    'errorMessage' => 'date is required and must be a valid date.',                
                    'status' => 400,
                ],
                [
                    'errorMessage' => 'serviceId should be a comma-separated string of integers.',                
                    'status' => 400,
                ]
            ],
            'status' => 400
        ];
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testDateAndServiceCountMissing()
    {
        $parameters = [
            'officeId' => '10546',
            'serviceId' => '1063423',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                [
                    'errorMessage' => 'date is required and must be a valid date.',                
                    'status' => 400,
                ],
                [
                    'errorMessage' => 'serviceCount should be a comma-separated string of integers.',                
                    'status' => 400,
                ]
            ],
            'status' => 400
        ];
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testOfficeIdAndServiceIdMissing()
    {
        $parameters = [
            'date' => '3000-09-21',
            'serviceCount' => '1',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                [
                    'errorMessage' => 'officeId should be a 32-bit integer.',                
                    'status' => 400,
                ],
                [
                    'errorMessage' => 'serviceId should be a comma-separated string of integers.',                
                    'status' => 400,
                ]
            ],
            'status' => 400
        ];
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testOfficeIdAndServiceCountMissing()
    {
        $parameters = [
            'date' => '3000-09-21',
            'serviceId' => '1063423',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                [
                    'errorMessage' => 'officeId should be a 32-bit integer.',                
                    'status' => 400,
                ],
                [
                    'errorMessage' => 'serviceCount should be a comma-separated string of integers.',                
                    'status' => 400,
                ]
            ],
            'status' => 400
        ];
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testServiceIdAndServiceCountMissing()
    {
        $parameters = [
            'date' => '3000-09-21',
            'officeId' => '10546',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                [
                    'errorMessage' => 'serviceId should be a comma-separated string of integers.',                
                    'status' => 400,
                ],
                [
                    'errorMessage' => 'serviceCount should be a comma-separated string of integers.',                
                    'status' => 400,
                ]
            ],
            'status' => 400
        ];
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }
}
