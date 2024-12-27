<?php

namespace BO\Zmscitizenapi\Tests;

use BO\Zmscitizenapi\Localization\ErrorMessages;

class AppointmentReserveTest extends Base
{
    protected $classname = "\BO\Zmscitizenapi\Controllers\AppointmentReserve";

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
                    'response' => $this->readFixture("POST_reserve_appointment.json")
                ]
            ]
        );
    
        $parameters = [
            'officeId' => 10546,
            'serviceId' => ['1063423'],
            'serviceCount' => [0],
            'timestamp' => "32526616522",
            'captchaSolution' => null
        ];
    
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'processId' => 101002,
            'timestamp' => '32526616522',
            'authKey' => 'fb43',
            'familyName' => 'TEST_USER',
            'customTextfield' => '',
            'email' => 'test@muenchen.de',
            'telephone' => '123456789',
            'officeName' => null,
            'officeId' => 10546,
            'scope' => [
                'id' => 58,
                'provider' => [
                    'id' => 10546,
                    "name" => null,
                    "source" => "dldb",
                    "contact" => null
                ],
                'shortName' => 'Gewerbemeldungen',
                'telephoneActivated' => false,
                'telephoneRequired' => true,
                'customTextfieldActivated' => false,
                'customTextfieldRequired' => true,
                'customTextfieldLabel' => '',
                'captchaActivatedRequired' => false,
                'displayInfo' => null
            ],
            'subRequestCounts' => [],
            'serviceId' => null,
            'serviceCount' => 0
        ];
    
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
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
            'officeId' => 10546,
            'serviceId' => ['1063423'],
            'serviceCount' => [0],
            'timestamp' => "32526616300",
            'captchaSolution' => null
        ];
    
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('appointmentNotAvailable')
            ]
        ];
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
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
                ErrorMessages::get('invalidOfficeId')
            ]
        ];
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testMissingServiceId()
    {
        $this->setApiCalls([]);

        $parameters = [
            'officeId' => 10546,
            'serviceCount' => [0],
            'timestamp' => "32526616522",
            'captchaSolution' => null
        ];

        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidServiceId')
            ]
        ];
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testMissingTimestamp()
    {
        $this->setApiCalls([]);

        $parameters = [
            'officeId' => 10546,
            'serviceId' => ['1063423'],
            'serviceCount' => [0],
            'captchaSolution' => null
        ];

        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidTimestamp')
            ]
        ];
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
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
                ErrorMessages::get('invalidOfficeId'),
                ErrorMessages::get('invalidServiceId')
            ]
        ];
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
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
                ErrorMessages::get('invalidOfficeId'),
                ErrorMessages::get('invalidTimestamp')
            ]
        ];
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testMissingServiceIdAndTimestamp()
    {
        $this->setApiCalls([]);

        $parameters = [
            'officeId' => 10546,
            'serviceCount' => [0],
            'captchaSolution' => null
        ];

        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidServiceId'),
                ErrorMessages::get('invalidTimestamp')
            ]
        ];
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testMissingAllFields()
    {
        $this->setApiCalls([]);

        $parameters = [];

        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidOfficeId'),
                ErrorMessages::get('invalidServiceId'),
                ErrorMessages::get('invalidTimestamp')
            ]
        ];
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
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
                ErrorMessages::get('invalidOfficeId')
            ]
        ];
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }
    
    public function testInvalidServiceIdFormat()
    {
        $this->setApiCalls([]);
    
        $parameters = [
            'officeId' => 10546,
            'serviceId' => ['invalid_service_id'],
            'serviceCount' => [0],
            'timestamp' => "32526616522",
            'captchaSolution' => null
        ];
    
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidServiceId')
            ]
        ];
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }
    
    public function testInvalidTimestampFormat()
    {
        $this->setApiCalls([]);
    
        $parameters = [
            'officeId' => 10546,
            'serviceId' => ['1063423'],
            'serviceCount' => [0],
            'timestamp' => 'invalid_timestamp',
            'captchaSolution' => null
        ];
    
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidTimestamp')
            ]
        ];
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }
    public function testEmptyServiceIdArray()
    {
        $this->setApiCalls([]);
    
        $parameters = [
            'officeId' => 10546,
            'serviceId' => [],
            'serviceCount' => [0],
            'timestamp' => "32526616522",
            'captchaSolution' => null
        ];
    
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidServiceId')
            ]
        ];
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }
    
    public function testInvalidServiceCount()
    {
        $this->setApiCalls([]);
    
        $parameters = [
            'officeId' => 10546,
            'serviceId' => ['1063423'],
            'serviceCount' => ['invalid'],
            'timestamp' => "32526616522",
            'captchaSolution' => null
        ];
    
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidServiceCount')
            ]
        ];
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

}
