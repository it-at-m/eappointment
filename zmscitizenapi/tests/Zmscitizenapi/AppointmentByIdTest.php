<?php

namespace BO\Zmscitizenapi\Tests;

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
                    '$schema' => 'https://schema.berlin.de/queuemanagement/provider.json',
                    'id' => 102522,
                    'name' => 'Bürgerbüro Orleansplatz DEV (KVR-II/231 DEV)',
                    'link' => 'https://service.berlin.de/standort/102522/',
                    'source' => 'dldb'
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
                [
                    'status' => 400,
                    'errorCode' => 'invalidProcessId',
                    'errorMessage' => 'processId should be a positive 32-bit integer.'
                ]
            ],
            'status' => 400
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
                [
                    'status' => 400,
                    'errorCode' => 'invalidAuthKey',
                    'errorMessage' => 'authKey should be a string.'
                ]
            ],
            'status' => 400
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
                [
                    'status' => 400,
                    'errorCode' => 'invalidProcessId',
                    'errorMessage' => 'processId should be a positive 32-bit integer.'
                ]
            ],
            'status' => 400
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
                [
                    'status' => 400,
                    'errorCode' => 'invalidAuthKey',
                    'errorMessage' => 'authKey should be a string.'
                ]
            ],
            'status' => 400
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
                [
                    'status' => 400,
                    'errorCode' => 'invalidProcessId',
                    'errorMessage' => 'processId should be a positive 32-bit integer.',
                ],
                [
                    'status' => 400,
                    'errorCode' => 'invalidAuthKey',
                    'errorMessage' => 'authKey should be a string.',
                ]
            ],
            'status' => 400
        ];
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);

    }

    public function testAppointmentNotFound()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/process/101002/fb43/',
                    'parameters' => [
                        'resolveReferences' => 2,
                    ],
                    'exception' => new \Exception('API-Error: Zu den angegebenen Daten konnte kein Termin gefunden werden.')
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
                [
                    'errorCode' => 'appointmentNotFound',
                    'errorMessage' => 'Termin wurde nicht gefunden.',
                    'status' => 404,
                ]
            ],
            'status' => 404
        ];
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

}