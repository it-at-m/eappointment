<?php

namespace BO\Zmscitizenapi\Tests\Controllers\Appointment;

use BO\Zmscitizenapi\Localization\ErrorMessages;
use BO\Zmscitizenapi\Tests\ControllerTestCase;

class AppointmentByIdControllerTest extends ControllerTestCase
{
    protected $classname = "\BO\Zmscitizenapi\Controllers\Appointment\AppointmentByIdController";

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
                    'url' => '/process/101002/fb43/',
                    'parameters' => [
                        'resolveReferences' => 2,
                    ],
                    'response' => $this->readFixture("GET_process.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/source/unittest/',
                    'parameters' => [
                        'resolveReferences' => 2,
                    ],
                    'response' => $this->readFixture("GET_SourceGet_dldb.json")
                ]
            ]
        );

        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43',
        ];
        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            "processId" => 101002,
            "timestamp" => "1724907600",
            "authKey" => "fb43",
            "familyName" => "Doe",
            "customTextfield" => "",
            "email" => "johndoe@example.com",
            "telephone" => "0123456789",
            "officeName" => "Bürgerbüro Orleansplatz DEV (KVR-II/231 DEV)",
            "officeId" => 102522,
            "status" => "confirmed",
            "scope" => [
                "id" => 64,
                "provider" => [
                    "id" => 102522,
                    "name" => "Bürgerbüro Orleansplatz DEV (KVR-II/231 DEV)",
                    "lat" => null,
                    "lon" => null,
                    "source" => "dldb",
                    "contact" => [
                        "city" => "Muenchen",
                        "country" => "Germany",
                        "name" => "Bürgerbüro Orleansplatz DEV (KVR-II/231 DEV)",
                        "postalCode" => "81667",
                        "region" => "Muenchen",
                        "street" => "Orleansstraße",
                        "streetNumber" => "50"
                    ]
                ],
                "shortName" => "DEVV",
                "telephoneActivated" => true,
                "telephoneRequired" => true,
                "customTextfieldActivated" => true,
                "customTextfieldRequired" => true,
                "customTextfieldLabel" => "Nachname des Kindes",
                "captchaActivatedRequired" => false,
                "displayInfo" => null
            ],
            "subRequestCounts" => [],
            "serviceId" => 1063424,
            "serviceCount" => 1
        ];        
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testMissingProcessId()
    {
        $parameters = [
            'authKey' => 'fb43',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidProcessId')
            ]
        ];
        $this->assertEquals(ErrorMessages::get('invalidProcessId')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testMissingAuthKey()
    {
        $parameters = [
            'processId' => '101002',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidAuthKey')
            ]
        ];
        $this->assertEquals(ErrorMessages::get('invalidAuthKey')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidProcessId()
    {
        $parameters = [
            'processId' => 'invalid',
            'authKey' => 'fb43',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidProcessId')
            ]
        ];
        $this->assertEquals(ErrorMessages::get('invalidProcessId')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidAuthKey()
    {
        $parameters = [
            'processId' => '101002',
            'authKey' => 12345,
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidAuthKey')
            ]
        ];
        $this->assertEquals(ErrorMessages::get('invalidAuthKey')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testBothParametersMissing()
    {
        $parameters = [];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidProcessId'),
                ErrorMessages::get('invalidAuthKey')
            ]
        ];
        $this->assertEquals(ErrorMessages::get('invalidProcessId')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidAuthKey')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);

    }

    public function testAppointmentNotFound()
    {
        $exception = new \BO\Zmsclient\Exception();
        $exception->template = 'BO\\Zmsapi\\Exception\\Process\\ProcessNotFound';
        
        $this->setApiCalls([
            [
                'function' => 'readGetResult',
                'url' => '/process/101002/fb43/',
                'parameters' => [
                    'resolveReferences' => 2,
                ],
                'exception' => $exception
            ]
        ]);
    
        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43',
        ];
    
        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedError = ErrorMessages::get('appointmentNotFound');
        $expectedResponse = [
            'errors' => [
                [
                    'errorCode' => $expectedError['errorCode'],
                    'errorMessage' => $expectedError['errorMessage'], 
                    'statusCode' => $expectedError['statusCode']
                ]
            ]
        ];
        $this->assertEquals($expectedError['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testAuthKeyMismatchException()
    {
        $exception = new \BO\Zmsclient\Exception();
        $exception->template = 'BO\\Zmsapi\\Exception\\Process\\AuthKeyMatchFailed';
    
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/process/101002/wrongKey/',
                    'parameters' => [
                        'resolveReferences' => 2,
                    ],
                    'exception' => $exception
                ]
            ]
        );
    
        $parameters = [
            'processId' => '101002',
            'authKey' => 'wrongKey',
        ];
    
        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('authKeyMismatch')
            ]
        ];
        $this->assertEquals(ErrorMessages::get('authKeyMismatch')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidApiClientException()
    {
        $exception = new \BO\Zmsclient\Exception();
        $exception->template = 'BO\\Zmsapi\\Exception\\Process\\ApiclientInvalid';
        
        $this->setApiCalls([
            [
                'function' => 'readGetResult',
                'url' => '/process/101002/fb43/',
                'parameters' => [
                    'resolveReferences' => 2,
                ],
                'exception' => $exception
            ]
        ]);

        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidApiClient')
            ]
        ];
        $this->assertEquals(ErrorMessages::get('invalidApiClient')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testDepartmentNotFoundException()
    {
        $exception = new \BO\Zmsclient\Exception();
        $exception->template = 'BO\\Zmsapi\\Exception\\Department\\DepartmentNotFound';
        
        $this->setApiCalls([
            [
                'function' => 'readGetResult',
                'url' => '/process/101002/fb43/',
                'parameters' => [
                    'resolveReferences' => 2,
                ],
                'exception' => $exception
            ]
        ]);
    
        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43',
        ];
    
        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedError = ErrorMessages::get('departmentNotFound');
        $expectedResponse = [
            'errors' => [
                [
                    'errorCode' => $expectedError['errorCode'],
                    'errorMessage' => $expectedError['errorMessage'],
                    'statusCode' => $expectedError['statusCode']
                ]
            ]
        ];
        $this->assertEquals($expectedError['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }
    
    public function testMailNotFoundException()
    {
        $exception = new \BO\Zmsclient\Exception();
        $exception->template = 'BO\\Zmsapi\\Exception\\Mail\\MailNotFound';
        
        $this->setApiCalls([
            [
                'function' => 'readGetResult',
                'url' => '/process/101002/fb43/',
                'parameters' => [
                    'resolveReferences' => 2,
                ],
                'exception' => $exception
            ]
        ]);
    
        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43',
        ];
    
        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedError = ErrorMessages::get('mailNotFound');
        $expectedResponse = [
            'errors' => [
                [
                    'errorCode' => $expectedError['errorCode'],
                    'errorMessage' => $expectedError['errorMessage'],
                    'statusCode' => $expectedError['statusCode']
                ]
            ]
        ];
        $this->assertEquals($expectedError['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }
    
    public function testOrganisationNotFoundException()
    {
        $exception = new \BO\Zmsclient\Exception();
        $exception->template = 'BO\\Zmsapi\\Exception\\Organisation\\OrganisationNotFound';
        
        $this->setApiCalls([
            [
                'function' => 'readGetResult',
                'url' => '/process/101002/fb43/',
                'parameters' => [
                    'resolveReferences' => 2,
                ],
                'exception' => $exception
            ]
        ]);
    
        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43',
        ];
    
        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedError = ErrorMessages::get('organisationNotFound');
        $expectedResponse = [
            'errors' => [
                [
                    'errorCode' => $expectedError['errorCode'],
                    'errorMessage' => $expectedError['errorMessage'],
                    'statusCode' => $expectedError['statusCode']
                ]
            ]
        ];
        $this->assertEquals($expectedError['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }
    
    public function testProviderNotFoundException()
    {
        $exception = new \BO\Zmsclient\Exception();
        $exception->template = 'BO\\Zmsapi\\Exception\\Provider\\ProviderNotFound';
        
        $this->setApiCalls([
            [
                'function' => 'readGetResult',
                'url' => '/process/101002/fb43/',
                'parameters' => [
                    'resolveReferences' => 2,
                ],
                'exception' => $exception
            ]
        ]);
    
        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43',
        ];
    
        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedError = ErrorMessages::get('providerNotFound');
        $expectedResponse = [
            'errors' => [
                [
                    'errorCode' => $expectedError['errorCode'],
                    'errorMessage' => $expectedError['errorMessage'],
                    'statusCode' => $expectedError['statusCode']
                ]
            ]
        ];
        $this->assertEquals($expectedError['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }
    
    public function testRequestNotFoundException()
    {
        $exception = new \BO\Zmsclient\Exception();
        $exception->template = 'BO\\Zmsapi\\Exception\\Request\\RequestNotFound';
        
        $this->setApiCalls([
            [
                'function' => 'readGetResult',
                'url' => '/process/101002/fb43/',
                'parameters' => [
                    'resolveReferences' => 2,
                ],
                'exception' => $exception
            ]
        ]);
    
        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43',
        ];
    
        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedError = ErrorMessages::get('requestNotFound');
        $expectedResponse = [
            'errors' => [
                [
                    'errorCode' => $expectedError['errorCode'],
                    'errorMessage' => $expectedError['errorMessage'],
                    'statusCode' => $expectedError['statusCode']
                ]
            ]
        ];
        $this->assertEquals($expectedError['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }
    
    public function testScopeNotFoundException()
    {
        $exception = new \BO\Zmsclient\Exception();
        $exception->template = 'BO\\Zmsapi\\Exception\\Scope\\ScopeNotFound';
        
        $this->setApiCalls([
            [
                'function' => 'readGetResult',
                'url' => '/process/101002/fb43/',
                'parameters' => [
                    'resolveReferences' => 2,
                ],
                'exception' => $exception
            ]
        ]);
    
        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43',
        ];
    
        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedError = ErrorMessages::get('scopeNotFound');
        $expectedResponse = [
            'errors' => [
                [
                    'errorCode' => $expectedError['errorCode'],
                    'errorMessage' => $expectedError['errorMessage'],
                    'statusCode' => $expectedError['statusCode']
                ]
            ]
        ];
        $this->assertEquals($expectedError['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }
    
    public function testProcessInvalidException()
    {
        $exception = new \BO\Zmsclient\Exception();
        $exception->template = 'BO\\Zmsapi\\Exception\\Process\\ProcessInvalid';
        
        $this->setApiCalls([
            [
                'function' => 'readGetResult',
                'url' => '/process/101002/fb43/',
                'parameters' => [
                    'resolveReferences' => 2,
                ],
                'exception' => $exception
            ]
        ]);
    
        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43',
        ];
    
        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedError = ErrorMessages::get('processInvalid');
        $expectedResponse = [
            'errors' => [
                [
                    'errorCode' => $expectedError['errorCode'],
                    'errorMessage' => $expectedError['errorMessage'],
                    'statusCode' => $expectedError['statusCode']
                ]
            ]
        ];
        $this->assertEquals($expectedError['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

}