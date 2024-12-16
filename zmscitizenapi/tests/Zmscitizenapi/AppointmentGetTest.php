<?php

namespace BO\Zmscitizenapi\Tests;

use BO\Zmscitizenapi\AppointmentGet;

class AppointmentGetTest extends Base
{
    protected $classname = "AppointmentGet";

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
            'id' => '101002',
            'authKey' => 'fb43',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'id' => '101002',
            'timestamp' => 1724907600,
            'authKey' => 'fb43',
            'familyName' => 'Doe',
            'customTextfield' => '',
            'email' => 'johndoe@example.com',
            'telephone' => '0123456789',
            'officeName' => 'Bürgerbüro Orleansplatz DEV (KVR-II/231 DEV)',
            'officeId' => '102522',
            'scope' => [
                '$schema' => 'https://schema.berlin.de/queuemanagement/scope.json',
                'id' => '64',
                'source' => 'dldb',
                'contact' => [
                    'name' => 'Bürgerbüro Orleansplatz DEV (KVR-II/231 DEV)',
                    'street' => 'Orleansstraße 50',
                    'email' => '',
                    'country' => 'Germany'
                ],
                'provider' => [
                    'contact' => [
                        'city' => 'Muenchen',
                        'country' => 'Germany',
                        'name' => 'Bürgerbüro Orleansplatz DEV (KVR-II/231 DEV)',
                        'postalCode' => '81667',
                        'region' => 'Muenchen',
                        'street' => 'Orleansstraße',
                        'streetNumber' => '50'
                    ],
                    'id' => '102522',
                    'link' => 'https://service.berlin.de/standort/102522/',
                    'name' => 'Bürgerbüro Orleansplatz DEV (KVR-II/231 DEV)',
                    'displayName' => 'Bürgerbüro Orleansplatz DEV',
                    'source' => 'dldb'
                ],
                'hint' => '',
                'lastChange' => 1724192287,
                'preferences' => [
                    'appointment' => [
                        'deallocationDuration' => '15',
                        'endInDaysDefault' => '60',
                        'multipleSlotsEnabled' => '0',
                        'reservationDuration' => '15',
                        'activationDuration' => '15',
                        'startInDaysDefault' => '2',
                        'notificationConfirmationEnabled' => '0',
                        'notificationHeadsUpEnabled' => '0'
                    ],
                    'client' => [
                        'alternateAppointmentUrl' => '',
                        'amendmentActivated' => '0',
                        'amendmentLabel' => '',
                        'emailFrom' => 'terminvereinbarung@muenchen.de',
                        'emailRequired' => '0',
                        'telephoneActivated' => '1',
                        'telephoneRequired' => '1',
                        'appointmentsPerMail' => '1',
                        'customTextfieldActivated' => '1',
                        'customTextfieldRequired' => '1',
                        'customTextfieldLabel' => 'Nachname des Kindes',
                        'captchaActivatedRequired' => '0',
                        'adminMailOnAppointment' => 0,
                        'adminMailOnDeleted' => 0,
                        'adminMailOnUpdated' => 0,
                        'adminMailOnMailSent' => 0
                    ],
                    'notifications' => [
                        'confirmationContent' => '',
                        'headsUpContent' => '',
                        'headsUpTime' => '10'
                    ],
                    'pickup' => [
                        'alternateName' => 'Ausgabe',
                        'isDefault' => '0'
                    ],
                    'queue' => [
                        'callCountMax' => '1',
                        'callDisplayText' => 'Herzlich Willkommen',
                        'firstNumber' => '1',
                        'lastNumber' => '999',
                        'maxNumberContingent' => '999',
                        'processingTimeAverage' => '12',
                        'publishWaitingTimeEnabled' => '0',
                        'statisticsEnabled' => '0'
                    ],
                    'survey' => [
                        'emailContent' => '',
                        'enabled' => '0',
                        'label' => ''
                    ],
                    'ticketprinter' => [
                        'buttonName' => 'Bürgerbüro Orleansplatz (KVR-II/231)',
                        'confirmationEnabled' => '0',
                        'deactivatedText' => 'dasdsa',
                        'notificationsAmendmentEnabled' => '0',
                        'notificationsEnabled' => '0',
                        'notificationsDelay' => '0'
                    ],
                    'workstation' => [
                        'emergencyEnabled' => '0',
                        'emergencyRefreshInterval' => '5'
                    ]
                ],
                'shortName' => 'DEVV',
                'status' => [
                    'emergency' => [
                        'activated' => '0'
                    ],
                    'queue' => [
                        'ghostWorkstationCount' => '-1',
                        'givenNumberCount' => '4',
                        'lastGivenNumber' => '4',
                        'lastGivenNumberTimestamp' => 1715292000
                    ],
                    'ticketprinter' => [
                        'deactivated' => '0'
                    ]
                ]
            ],
            'subRequestCounts' => [],
            'serviceId' => '1063424',
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
                    'errorMessage' => 'id should be a 32-bit integer.'
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
            'id' => '101002',
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
            'id' => 'invalid',
            'authKey' => 'fb43',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                [
                    'status' => 400,
                    'errorCode' => 'invalidProcessId',
                    'errorMessage' => 'id should be a 32-bit integer.'
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
            'id' => '101002',
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
                    'errorMessage' => 'id should be a 32-bit integer.',
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
            'id' => '101002',
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
