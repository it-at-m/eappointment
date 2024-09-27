<?php

namespace BO\Zmscitizenapi\Tests;

use Psr\Http\Message\ResponseInterface;

class AppointmentReserveTest extends Base
{
    protected $classname = "AppointmentReserve";

    public function testRendering()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/source/unittest/',
                    'parameters' => [
                        'resolveReferences' => 2,
                    ],
                    'response' => $this->readFixture("GET_reserve_SourceGet_dldb.json"),
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/free/',
                    'response' => $this->readFixture("GET_appointments_free.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/reserved/',
                    'response' => $this->readFixture("POST_reserve_timeslot.json")
                ]
            ]
        );

        $parameters = [
            'officeId' => '10546',
            'serviceId' => ['1063423'],
            'serviceCount' => [0],
            'timestamp' => "32526616522",
            'captchaSolution' => null
        ];

        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'processId' => '101142',
            'timestamp' => 32526616522,
            'authKey' => 'b93e',
            'familyName' => '',
            'customTextfield' => '',
            'email' => 'test@muenchen.de',
            'telephone' => '',
            'officeName' => null,
            'officeId' => '10546',
            'scope' => [
                'id' => '58',
                'provider' => [
                    'id' => '10546',
                    'source' => 'dldb'
                ],
                'shortName' => 'Gewerbemeldungen',
                'telephoneActivated' => '0',
                'telephoneRequired' => '1',
                'customTextfieldActivated' => '0',
                'customTextfieldRequired' => '1',
                'customTextfieldLabel' => '',
                'captchaActivatedRequired' => '0',
                'displayInfo' => null
            ],
            'subRequestCounts' => [],
            'serviceId' => null,
            'serviceCount' => 0
        ];
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody, true);
    }

    public function testAppointmentNotAvailable()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/source/unittest/',
                    'parameters' => [
                        'resolveReferences' => 2,
                    ],
                    'response' => $this->readFixture("GET_reserve_SourceGet_dldb.json"),
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/free/',
                    'response' => $this->readFixture("GET_appointments_free.json")
                ]
            ]
        );
    
        $parameters = [
            'officeId' => '10546',
            'serviceId' => ['1063423'],
            'serviceCount' => [0],
            'timestamp' => "32526616300",
            'captchaSolution' => null
        ];
    
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                [
                    'errorCode' => 'appointmentNotAvailable',
                    'errorMessage' => 'Der von Ihnen gewählte Termin ist leider nicht mehr verfügbar.',                
                    'status' => 404,
                ]
            ],
            'status' => 404
        ];
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody, true);
    }

    public function testMissingOfficeId()
    {
        $this->setApiCalls([]);

        $parameters = [
            'serviceId' => ['1063423'],
            'serviceCount' => [0],
            'timestamp' => "32526616522",
            'captchaSolution' => null
        ];

        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                [
                    'status' => 400,
                    'errorMessage' => 'Missing officeId.',
                ]
            ],
            'status' => 400
        ];
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody, true);
    }

    public function testMissingServiceId()
    {
        $this->setApiCalls([]);

        $parameters = [
            'officeId' => '10546',
            'serviceCount' => [0],
            'timestamp' => "32526616522",
            'captchaSolution' => null
        ];

        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                [
                    'status' => 400,
                    'errorMessage' => 'Missing serviceId.',
                ]
            ],
            'status' => 400
        ];
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody, true);
    }

    public function testMissingTimestamp()
    {
        $this->setApiCalls([]);

        $parameters = [
            'officeId' => '10546',
            'serviceId' => ['1063423'],
            'serviceCount' => [0],
            'captchaSolution' => null
        ];

        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                [
                    'status' => 400,
                    'errorMessage' => 'Missing timestamp.',
                ]
            ],
            'status' => 400
        ];
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody, true);
    }

    public function testMissingOfficeIdAndServiceId()
    {
        $this->setApiCalls([]);

        $parameters = [
            'serviceCount' => [0],
            'timestamp' => "32526616522",
            'captchaSolution' => null
        ];

        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                [
                    'status' => 400,
                    'errorMessage' => 'Missing officeId.',
                ],
                [
                    'status' => 400,
                    'errorMessage' => 'Missing serviceId.',
                ]
            ],
            'status' => 400
        ];
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody, true);
    }

    public function testMissingOfficeIdAndTimestamp()
    {
        $this->setApiCalls([]);

        $parameters = [
            'serviceId' => ['1063423'],
            'serviceCount' => [0],
            'captchaSolution' => null
        ];

        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                [
                    'status' => 400,
                    'errorMessage' => 'Missing officeId.',
                ],
                [
                    'status' => 400,
                    'errorMessage' => 'Missing timestamp.',
                ]
            ],
            'status' => 400
        ];
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody, true);
    }

    public function testMissingServiceIdAndTimestamp()
    {
        $this->setApiCalls([]);

        $parameters = [
            'officeId' => '10546',
            'serviceCount' => [0],
            'captchaSolution' => null
        ];

        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                [
                    'status' => 400,
                    'errorMessage' => 'Missing serviceId.',
                ],
                [
                    'status' => 400,
                    'errorMessage' => 'Missing timestamp.',
                ]
            ],
            'status' => 400
        ];
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody, true);
    }

    public function testMissingAllFields()
    {
        $this->setApiCalls([]);

        $parameters = [];

        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                [
                    'status' => 400,
                    'errorMessage' => 'Missing officeId.',
                ],
                [
                    'status' => 400,
                    'errorMessage' => 'Missing serviceId.',
                ],
                [
                    'status' => 400,
                    'errorMessage' => 'Missing timestamp.',
                ]
            ],
            'status' => 400
        ];
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody, true);
    }

    public function testInvalidOfficeIdFormat()
    {
        $this->setApiCalls([]);
    
        $parameters = [
            'officeId' => 'invalid_id',
            'serviceId' => ['1063423'],
            'serviceCount' => [0],
            'timestamp' => "32526616522",
            'captchaSolution' => null
        ];
    
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                [
                    'status' => 400,
                    'errorMessage' => 'Invalid officeId format. It should be a numeric value.',
                ]
            ],
            'status' => 400
        ];
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody, true);
    }
    
    public function testInvalidServiceIdFormat()
    {
        $this->setApiCalls([]);
    
        $parameters = [
            'officeId' => '10546',
            'serviceId' => ['invalid_service_id'],
            'serviceCount' => [0],
            'timestamp' => "32526616522",
            'captchaSolution' => null
        ];
    
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                [
                    'status' => 400,
                    'errorMessage' => 'Invalid serviceId format. It should be an array of numeric values.',
                ]
            ],
            'status' => 400
        ];
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody, true);
    }
    
    public function testInvalidTimestampFormat()
    {
        $this->setApiCalls([]);
    
        $parameters = [
            'officeId' => '10546',
            'serviceId' => ['1063423'],
            'serviceCount' => [0],
            'timestamp' => 'invalid_timestamp',
            'captchaSolution' => null
        ];
    
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                [
                    'status' => 400,
                    'errorMessage' => 'Invalid timestamp format. It should be a positive numeric value.',
                ]
            ],
            'status' => 400
        ];
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody, true);
    }
    public function testEmptyServiceIdArray()
    {
        $this->setApiCalls([]);
    
        $parameters = [
            'officeId' => '10546',
            'serviceId' => [],
            'serviceCount' => [0],
            'timestamp' => "32526616522",
            'captchaSolution' => null
        ];
    
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                [
                    'status' => 400,
                    'errorMessage' => 'Missing serviceId.',
                ]
            ],
            'status' => 400
        ];
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody, true);
    }
    
    public function testInvalidServiceCount()
    {
        $this->setApiCalls([]);
    
        $parameters = [
            'officeId' => '10546',
            'serviceId' => ['1063423'],
            'serviceCount' => ['invalid'],
            'timestamp' => "32526616522",
            'captchaSolution' => null
        ];
    
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                [
                    'status' => 400,
                    'errorMessage' => 'Invalid serviceCount format. It should be an array of non-negative numeric values.',
                ]
            ],
            'status' => 400
        ];
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody, true);
    }

}
