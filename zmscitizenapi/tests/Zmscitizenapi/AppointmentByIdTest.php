<?php

namespace BO\Zmscitizenapi\Tests;
use BO\Zmscitizenapi\Localization\ErrorMessages;

class AppointmentByIdTest extends Base
{
    protected $classname = "\BO\Zmscitizenapi\Controllers\AppointmentById";

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
            'processId' => 101002,
            'timestamp' => '1724907600',
            'authKey' => 'fb43',
            'familyName' => 'Doe',
            'customTextfield' => '',
            'email' => 'johndoe@example.com',
            'telephone' => '0123456789',
            'officeName' => 'Bürgerbüro Orleansplatz DEV (KVR-II/231 DEV)',
            'officeId' => 102522,
            'scope' => [
                'id' => 64,
                'provider' => [
                    'id' => 102522,
                    'name' => 'Bürgerbüro Orleansplatz DEV (KVR-II/231 DEV)',
                    'source' => 'dldb',
                    'contact' => [
                        'city' => 'Muenchen',
                        'country' => 'Germany',
                        'name' => 'Bürgerbüro Orleansplatz DEV (KVR-II/231 DEV)',
                        'postalCode' => '81667',
                        'region' => 'Muenchen',
                        'street' => 'Orleansstraße',
                        'streetNumber' => '50'
                    ]
                ],
                'shortName' => 'DEVV',
                'telephoneActivated' => null,
                'telephoneRequired' => null,
                'customTextfieldActivated' => null,
                'customTextfieldRequired' => null,
                'customTextfieldLabel' => null,
                'captchaActivatedRequired' => null,
                'displayInfo' => null
            ],
            'subRequestCounts' => [],
            'serviceId' => 1063424,
            'serviceCount' => 1
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
        $this->assertEquals(400, $response->getStatusCode());
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
        $this->assertEquals(400, $response->getStatusCode());
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
        $this->assertEquals(400, $response->getStatusCode());
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
        $this->assertEquals(400, $response->getStatusCode());
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
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);

    }

    public function testAppointmentNotFound()
    {

        $exception = new \BO\Zmsclient\Exception();
        $exception->template = 'BO\\Zmsapi\\Exception\\Process\\ProcessNotFound';
        
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/process/101002/fb43/',
                    'parameters' => [
                        'resolveReferences' => 2,
                    ],
                    'exception' => $exception
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
            'errors' => [
                ErrorMessages::get('appointmentNotFound')
            ]
        ];
        $this->assertEquals(404, $response->getStatusCode());
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
        $this->assertEquals(406, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    } 

}