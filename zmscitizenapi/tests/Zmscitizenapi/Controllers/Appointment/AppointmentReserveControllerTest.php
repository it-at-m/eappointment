<?php

namespace BO\Zmscitizenapi\Tests\Controllers\Appointment;

use BO\Zmscitizenapi\Localization\ErrorMessages;
use BO\Zmscitizenapi\Tests\ControllerTestCase;

class AppointmentReserveControllerTest extends ControllerTestCase
{
    protected $classname = "\BO\Zmscitizenapi\Controllers\Appointment\AppointmentReserveController";

    public function setUp(): void
    {
        parent::setUp();
        
        \App::$source_name = 'unittest';

        if (\App::$cache) {
            \App::$cache->clear();
        }
    }

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
                    'url' => '/process/status/free/unique/',
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
            'timestamp' => "32526616522"
        ];
    
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string)$response->getBody(), true);

        $this->assertArrayHasKey('captchaToken', $responseBody);
        $this->assertIsString($responseBody['captchaToken']);
        unset($responseBody['captchaToken']);

        $expectedResponse = [
            "processId" => 101002,
            "timestamp" => "32526616522",
            "authKey" => "fb43",
            "familyName" => "TEST_USER",
            "customTextfield" => "",
            "customTextfield2" => "",
            "email" => "test@muenchen.de",
            "telephone" => "123456789",
            "officeName" => null,
            "officeId" => 10546,
            "status" => "reserved",
            "scope" => [
                "id" => 58,
                "provider" => [
                    "id" => 10546,
                    "name" => "Gewerbeamt (KVR-III/21)",
                    "displayName" => "Gewerbeamt",
                    "lat" => null,
                    "lon" => null,
                    "source" => "dldb",
                    "contact" => [
                        "city" => "Muenchen",
                        "country" => "Germany",
                        "name" => "Gewerbeamt (KVR-III/21)",
                        "postalCode" => "81371",
                        "region" => "Muenchen",
                        "street" => "Implerstraße",
                        "streetNumber" => "11"
                    ]
                ],
                "shortName" => "Gewerbemeldungen",
                "emailFrom" => "no-reply@muenchen.de",
                'emailRequired' => false,
                "telephoneActivated" => false,
                "telephoneRequired" => true,
                "customTextfieldActivated" => false,
                "customTextfieldRequired" => true,
                "customTextfieldLabel" => "",
                'customTextfield2Activated' => false,
                'customTextfield2Required' => true,
                'customTextfield2Label' => "",
                "captchaActivatedRequired" => false,
                "displayInfo" => null,
                "slotsPerAppointment" => null,
                "appointmentsPerMail" => null,
                "whitelistedMails" => null,
                "reservationDuration" => null
            ],
            "subRequestCounts" => [],
            "serviceId" => 0,
            "serviceName" => null,
            "serviceCount" => 0,
            "slotCount" => 4
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
                    'url' => '/process/status/free/unique/',
                    'response' => $this->readFixture("GET_appointments_free.json")
                ]
            ]
        );
    
        $parameters = [
            'officeId' => 10546,
            'serviceId' => ['1063423'],
            'serviceCount' => [0],
            'timestamp' => "32526616300"
        ];
    
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('appointmentNotAvailable')
            ]
        ];
        $this->assertEquals(ErrorMessages::get('appointmentNotAvailable')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testMissingOfficeId()
    {
        $this->setApiCalls([]);

        $parameters = [
            'serviceId' => ['1063423'],
            'serviceCount' => [0],
            'timestamp' => "32526616522"
        ];

        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidOfficeId')
            ]
        ];
        $this->assertEquals(ErrorMessages::get('invalidOfficeId')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testMissingServiceId()
    {
        $this->setApiCalls([]);

        $parameters = [
            'officeId' => 10546,
            'serviceCount' => [0],
            'timestamp' => "32526616522"
        ];

        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidServiceId')
            ]
        ];
        $this->assertEquals(ErrorMessages::get('invalidServiceId')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testMissingTimestamp()
    {
        $this->setApiCalls([]);

        $parameters = [
            'officeId' => 10546,
            'serviceId' => ['1063423'],
            'serviceCount' => [0]
        ];

        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidTimestamp')
            ]
        ];
        $this->assertEquals(ErrorMessages::get('invalidTimestamp')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testMissingOfficeIdAndServiceId()
    {
        $this->setApiCalls([]);

        $parameters = [
            'serviceCount' => [0],
            'timestamp' => "32526616522"
        ];

        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidOfficeId'),
                ErrorMessages::get('invalidServiceId')
            ]
        ];
        $this->assertEquals(ErrorMessages::get('invalidOfficeId')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidServiceId')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testMissingOfficeIdAndTimestamp()
    {
        $this->setApiCalls([]);

        $parameters = [
            'serviceId' => ['1063423'],
            'serviceCount' => [0]
        ];

        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidOfficeId'),
                ErrorMessages::get('invalidTimestamp')
            ]
        ];
        $this->assertEquals(ErrorMessages::get('invalidOfficeId')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidTimestamp')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testMissingServiceIdAndTimestamp()
    {
        $this->setApiCalls([]);

        $parameters = [
            'officeId' => 10546,
            'serviceCount' => [0]
        ];

        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidServiceId'),
                ErrorMessages::get('invalidTimestamp')
            ]
        ];
        $this->assertEquals(ErrorMessages::get('invalidServiceId')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidTimestamp')['statusCode'], $response->getStatusCode());
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
        $this->assertEquals(ErrorMessages::get('invalidOfficeId')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidServiceId')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidTimestamp')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidOfficeIdFormat()
    {
        $this->setApiCalls([]);
    
        $parameters = [
            'officeId' => 'invalid_id',
            'serviceId' => ['1063423'],
            'serviceCount' => [0],
            'timestamp' => "32526616522"
        ];
    
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidOfficeId')
            ]
        ];
        $this->assertEquals(ErrorMessages::get('invalidOfficeId')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }
    
    public function testInvalidServiceIdFormat()
    {
        $this->setApiCalls([]);
    
        $parameters = [
            'officeId' => 10546,
            'serviceId' => ['invalid_service_id'],
            'serviceCount' => [0],
            'timestamp' => "32526616522"
        ];
    
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidServiceId')
            ]
        ];
        $this->assertEquals(ErrorMessages::get('invalidServiceId')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }
    
    public function testInvalidTimestampFormat()
    {
        $this->setApiCalls([]);
    
        $parameters = [
            'officeId' => 10546,
            'serviceId' => ['1063423'],
            'serviceCount' => [0],
            'timestamp' => 'invalid_timestamp'
        ];
    
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidTimestamp')
            ]
        ];
        $this->assertEquals(ErrorMessages::get('invalidTimestamp')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }
    public function testEmptyServiceIdArray()
    {
        $this->setApiCalls([]);
    
        $parameters = [
            'officeId' => 10546,
            'serviceId' => [],
            'serviceCount' => [0],
            'timestamp' => "32526616522"
        ];
    
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidServiceId')
            ]
        ];
        $this->assertEquals(ErrorMessages::get('invalidServiceId')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }
    
    public function testInvalidServiceCount()
    {
        $this->setApiCalls([]);
    
        $parameters = [
            'officeId' => 10546,
            'serviceId' => ['1063423'],
            'serviceCount' => ['invalid'],
            'timestamp' => "32526616522"
        ];
    
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidServiceCount')
            ]
        ];
        $this->assertEquals(ErrorMessages::get('invalidServiceCount')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testProcessInvalid()
    {
        $exception = new \BO\Zmsclient\Exception();
        $exception->template = 'BO\\Zmsapi\\Exception\\Process\\ProcessInvalid';
        
    
        $this->setApiCalls([
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
                'url' => '/process/status/free/unique/',
                'response' => $this->readFixture("GET_appointments_free.json")
            ],
            [
                'function' => 'readPostResult',
                'url' => '/process/status/reserved/',
                'exception' => $exception
            ]
        ]);
    
        $parameters = [
            'officeId' => 10546,
            'serviceId' => ['1063423'],
            'serviceCount' => [0],
            'timestamp' => "32526616522"
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('processInvalid')
            ]
        ];
        $this->assertEquals(ErrorMessages::get('processInvalid')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testProcessAlreadyExists()
    {
        $exception = new \BO\Zmsclient\Exception();
        $exception->template = 'BO\\Zmsapi\\Exception\\Process\\ProcessAlreadyExists';
        
        $this->setApiCalls([
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
                'url' => '/process/status/free/unique/',
                'response' => $this->readFixture("GET_appointments_free.json")
            ],
            [
                'function' => 'readPostResult',
                'url' => '/process/status/reserved/',
                'exception' => $exception
            ]
        ]);
    
        $parameters = [
            'officeId' => 10546,
            'serviceId' => ['1063423'],
            'serviceCount' => [0],
            'timestamp' => "32526616522"
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('processAlreadyExists')
            ]
        ];
        $this->assertEquals(ErrorMessages::get('processAlreadyExists')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

}
