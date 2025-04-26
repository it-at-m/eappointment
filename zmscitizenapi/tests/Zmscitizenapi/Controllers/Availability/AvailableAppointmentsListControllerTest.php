<?php

namespace BO\Zmscitizenapi\Tests\Controllers\Availability;

use BO\Zmscitizenapi\Localization\ErrorMessages;
use BO\Zmscitizenapi\Tests\ControllerTestCase;

class AvailableAppointmentsListControllerTest extends ControllerTestCase
{
    protected $classname = "\BO\Zmscitizenapi\Controllers\Availability\AvailableAppointmentsListController";

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
                    'response' => $this->readFixture("GET_SourceGet_dldb.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/free/',
                    'response' => $this->readFixture("GET_appointments.json")
                ]
            ]
        );

        $parameters = [
            'date' => '3000-09-21',
            'officeId' => 10546,
            'serviceId' => '1063423',
            'serviceCount' => '1',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'appointmentTimestamps' => [32526616522]
        ];
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }


    public function testEmptyAppointments()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/source/unittest/',
                    'parameters' => [
                        'resolveReferences' => 2,
                    ],
                    'response' => $this->readFixture("GET_SourceGet_dldb.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/free/',
                    'response' => $this->readFixture("GET_appointments_empty.json")
                ]
            ]
        );

        $parameters = [
            'date' => '3000-09-21',
            'officeId' => 10546,
            'serviceId' => '1063423',
            'serviceCount' => '1',
        ];

        $response = $this->render([], $parameters, []);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('appointmentNotAvailable')
            ]
        ];
        $this->assertEquals(ErrorMessages::get('appointmentNotAvailable')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, json_decode((string) $response->getBody(), true));
    }

    public function testDateMissing()
    {
        $parameters = [
            'officeId' => 10546,
            'serviceId' => '1063423',
            'serviceCount' => '1',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidDate')
            ]
        ];
        $this->assertEquals(ErrorMessages::get('invalidDate')['statusCode'], $response->getStatusCode());
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
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidOfficeId')
            ]
        ];
        $this->assertEquals(ErrorMessages::get('invalidOfficeId')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testServiceIdMissing()
    {
        $parameters = [
            'date' => '3000-09-21',
            'officeId' => 10546,
            'serviceCount' => '1',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidServiceId')
            ]
        ];
        $this->assertEquals(ErrorMessages::get('invalidServiceId')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testServiceCountMissing()
    {
        $parameters = [
            'date' => '3000-09-21',
            'officeId' => 10546,
            'serviceId' => '1063423',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidServiceCount')
            ]
        ];
        $this->assertEquals(ErrorMessages::get('invalidServiceCount')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testDateAndOfficeIdMissing()
    {
        $parameters = [
            'serviceId' => '1063423',
            'serviceCount' => '1',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidDate'),
                ErrorMessages::get('invalidOfficeId')
            ]
        ];
        $this->assertEquals(ErrorMessages::get('invalidDate')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidOfficeId')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testDateAndServiceIdMissing()
    {
        $parameters = [
            'officeId' => 10546,
            'serviceCount' => '1',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidDate'),
                ErrorMessages::get('invalidServiceId')
            ]
        ];
        $this->assertEquals(ErrorMessages::get('invalidDate')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidServiceId')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testDateAndServiceCountMissing()
    {
        $parameters = [
            'officeId' => 10546,
            'serviceId' => '1063423',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidDate'),
                ErrorMessages::get('invalidServiceCount')
            ]
        ];
        $this->assertEquals(ErrorMessages::get('invalidDate')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidServiceCount')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testOfficeIdAndServiceIdMissing()
    {
        $parameters = [
            'date' => '3000-09-21',
            'serviceCount' => '1',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);
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

    public function testOfficeIdAndServiceCountMissing()
    {
        $parameters = [
            'date' => '3000-09-21',
            'serviceId' => '1063423',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidOfficeId'),
                ErrorMessages::get('invalidServiceCount')
            ]
        ];
        $this->assertEquals(ErrorMessages::get('invalidOfficeId')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidServiceCount')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testServiceIdAndServiceCountMissing()
    {
        $parameters = [
            'date' => '3000-09-21',
            'officeId' => 10546,
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidServiceId'),
                ErrorMessages::get('invalidServiceCount')
            ]
        ];
        $this->assertEquals(ErrorMessages::get('invalidServiceId')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidServiceCount')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidDateFormat()
    {
        $parameters = [
            'date' => '21-09-3000',
            'officeId' => 10546,
            'serviceId' => '1063423',
            'serviceCount' => '1',
        ];
    
        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidDate')
            ]
        ];
        $this->assertEquals(ErrorMessages::get('invalidDate')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }
  
    public function testInvalidServiceIdFormat()
    {
        $parameters = [
            'date' => '3000-09-21',
            'officeId' => 10546,
            'serviceId' => 'invalid',
            'serviceCount' => '1',
        ];
    
        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidServiceId')
            ]
        ];
        $this->assertEquals(ErrorMessages::get('invalidServiceId')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }
    
    public function testInvalidOfficeIdFormat()
    {
        $parameters = [
            'date' => '3000-09-21',
            'officeId' => 'invalid',
            'serviceId' => '1063423',
            'serviceCount' => '1',
        ];
    
        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidOfficeId')
            ]
        ];
        $this->assertEquals(ErrorMessages::get('invalidOfficeId')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }
}
