<?php

namespace BO\Zmscitizenapi\Tests;

class AppointmentUpdateTest extends Base
{

    protected $classname = "AppointmentUpdate";

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
                    'function' => 'readPostResult',
                    'url' => '/process/101002/fb43/',
                    'response' => $this->readFixture("POST_update_appointment.json")
                ]
            ]
        );

        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43',
            'familyName' => 'Smith',
            'email' => "default@example.com",
            'telephone' => '123456789',
            'customTextfield' => "Some custom text",
        ];

        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            "processId" => "101002",
            "timestamp" => 1727865900,
            "authKey" => "fb43",
            "familyName" => "Smith",
            "customTextfield" => "Some custom text",
            "email" => "default@example.com",
            "telephone" => "123456789",
            "officeName" => null,
            "officeId" => null,
            "scope" => [
                '$schema' => "https://schema.berlin.de/queuemanagement/scope.json",
                "id" => 0,
                "source" => "dldb"
            ],
            "subRequestCounts" => [],
            "serviceId" => "10242339",
            "serviceCount" => 1
        ];

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);

    }

    public function testTooManyEmailsAtLocation()
    {

        $exception = new \BO\Zmsclient\Exception();
        $exception->template = 'BO\\Zmsapi\\Exception\\Process\\MoreThanAllowedAppointmentsPerMail';

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
                    'function' => 'readPostResult',
                    'url' => '/process/101002/fb43/',
                    'exception' => $exception
                ]
            ]
        );

        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43',
            'familyName' => 'Smith',
            'email' => "default@example.com",
            'telephone' => '123456789',
            'customTextfield' => "Some custom text",
        ];

        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                [
                    'errorCode' => 'tooManyAppointmentsWithSameMail',
                    'errorMessage' => 'Zu viele Termine mit gleicher E-Mail- Adresse.',
                    'status' => 406,
                ]
            ],
            'status' => 406
        ];

        $this->assertEquals(406, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testAppointmentNotFound()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/process/101003/fb43/',
                    'parameters' => [
                        'resolveReferences' => 2,
                    ],
                    'exception' => new \Exception('API-Error: Zu den angegebenen Daten konnte kein Termin gefunden werden.')
                ]
            ]
        );

        $parameters = [
            'processId' => '101003',
            'authKey' => 'fb43',
            'familyName' => 'Smith',
            'email' => "default@example.com",
            'telephone' => '123456789',
            'customTextfield' => "Some custom text",
        ];

        $response = $this->render([], $parameters, [], 'POST');
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

    public function testInvalidProcessid_InvalidAuthkey_InvalidFamilyname_InvalidEmail_InvalidTelephone_InvalidCustomtextfield()
    {
        $parameters = [
            'processId' => null,
            'authKey' => '',
            'familyName' => '',
            'email' => 'invalid-email',
            'telephone' => '123',
            'customTextfield' => 123
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $expectedResponse = [
            'errors' => [
                ['status' => 400, 'errorMessage' => 'processId should be a positive 32-bit integer.'],
                ['status' => 400, 'errorMessage' => 'authKey should be a non-empty string.'],
                ['status' => 400, 'errorMessage' => 'familyName should be a non-empty string.'],
                ['status' => 400, 'errorMessage' => 'email should be a valid email address.'],
                ['status' => 400, 'errorMessage' => 'telephone should be a numeric string between 7 and 15 digits.'],
                ['status' => 400, 'errorMessage' => 'customTextfield should be a string.']
            ],
            'status' => 400
        ];

        $this->assertEquals($expectedResponse['status'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidProcessid_InvalidAuthkey_InvalidFamilyname_InvalidEmail_InvalidTelephone_ValidCustomtextfield()
    {
        $parameters = [
            'processId' => null,
            'authKey' => '',
            'familyName' => '',
            'email' => 'invalid-email',
            'telephone' => '123',
            'customTextfield' => 'Some custom text'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $expectedResponse = [
            'errors' => [
                ['status' => 400, 'errorMessage' => 'processId should be a positive 32-bit integer.'],
                ['status' => 400, 'errorMessage' => 'authKey should be a non-empty string.'],
                ['status' => 400, 'errorMessage' => 'familyName should be a non-empty string.'],
                ['status' => 400, 'errorMessage' => 'email should be a valid email address.'],
                ['status' => 400, 'errorMessage' => 'telephone should be a numeric string between 7 and 15 digits.']
            ],
            'status' => 400
        ];

        $this->assertEquals($expectedResponse['status'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidProcessid_InvalidAuthkey_InvalidFamilyname_InvalidEmail_ValidTelephone_InvalidCustomtextfield()
    {
        $parameters = [
            'processId' => null,
            'authKey' => '',
            'familyName' => '',
            'email' => 'invalid-email',
            'telephone' => '123456789',
            'customTextfield' => 123
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $expectedResponse = [
            'errors' => [
                ['status' => 400, 'errorMessage' => 'processId should be a positive 32-bit integer.'],
                ['status' => 400, 'errorMessage' => 'authKey should be a non-empty string.'],
                ['status' => 400, 'errorMessage' => 'familyName should be a non-empty string.'],
                ['status' => 400, 'errorMessage' => 'email should be a valid email address.'],
                ['status' => 400, 'errorMessage' => 'customTextfield should be a string.']
            ],
            'status' => 400
        ];

        $this->assertEquals($expectedResponse['status'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidProcessid_InvalidAuthkey_InvalidFamilyname_InvalidEmail_ValidTelephone_ValidCustomtextfield()
    {
        $parameters = [
            'processId' => null,
            'authKey' => '',
            'familyName' => '',
            'email' => 'invalid-email',
            'telephone' => '123456789',
            'customTextfield' => 'Some custom text'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $expectedResponse = [
            'errors' => [
                ['status' => 400, 'errorMessage' => 'processId should be a positive 32-bit integer.'],
                ['status' => 400, 'errorMessage' => 'authKey should be a non-empty string.'],
                ['status' => 400, 'errorMessage' => 'familyName should be a non-empty string.'],
                ['status' => 400, 'errorMessage' => 'email should be a valid email address.']
            ],
            'status' => 400
        ];

        $this->assertEquals($expectedResponse['status'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidProcessid_InvalidAuthkey_InvalidFamilyname_ValidEmail_InvalidTelephone_InvalidCustomtextfield()
    {
        $parameters = [
            'processId' => null,
            'authKey' => '',
            'familyName' => '',
            'email' => 'default@example.com',
            'telephone' => '123',
            'customTextfield' => 123
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $expectedResponse = [
            'errors' => [
                ['status' => 400, 'errorMessage' => 'processId should be a positive 32-bit integer.'],
                ['status' => 400, 'errorMessage' => 'authKey should be a non-empty string.'],
                ['status' => 400, 'errorMessage' => 'familyName should be a non-empty string.'],
                ['status' => 400, 'errorMessage' => 'telephone should be a numeric string between 7 and 15 digits.'],
                ['status' => 400, 'errorMessage' => 'customTextfield should be a string.']
            ],
            'status' => 400
        ];

        $this->assertEquals($expectedResponse['status'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidProcessid_InvalidAuthkey_InvalidFamilyname_ValidEmail_InvalidTelephone_ValidCustomtextfield()
    {
        $parameters = [
            'processId' => null,
            'authKey' => '',
            'familyName' => '',
            'email' => 'default@example.com',
            'telephone' => '123',
            'customTextfield' => 'Some custom text'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $expectedResponse = [
            'errors' => [
                ['status' => 400, 'errorMessage' => 'processId should be a positive 32-bit integer.'],
                ['status' => 400, 'errorMessage' => 'authKey should be a non-empty string.'],
                ['status' => 400, 'errorMessage' => 'familyName should be a non-empty string.'],
                ['status' => 400, 'errorMessage' => 'telephone should be a numeric string between 7 and 15 digits.']
            ],
            'status' => 400
        ];

        $this->assertEquals($expectedResponse['status'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidProcessid_InvalidAuthkey_InvalidFamilyname_ValidEmail_ValidTelephone_InvalidCustomtextfield()
    {
        $parameters = [
            'processId' => null,
            'authKey' => '',
            'familyName' => '',
            'email' => 'default@example.com',
            'telephone' => '123456789',
            'customTextfield' => 123
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $expectedResponse = [
            'errors' => [
                ['status' => 400, 'errorMessage' => 'processId should be a positive 32-bit integer.'],
                ['status' => 400, 'errorMessage' => 'authKey should be a non-empty string.'],
                ['status' => 400, 'errorMessage' => 'familyName should be a non-empty string.'],
                ['status' => 400, 'errorMessage' => 'customTextfield should be a string.']
            ],
            'status' => 400
        ];

        $this->assertEquals($expectedResponse['status'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidProcessid_InvalidAuthkey_InvalidFamilyname_ValidEmail_ValidTelephone_ValidCustomtextfield()
    {
        $parameters = [
            'processId' => null,
            'authKey' => '',
            'familyName' => '',
            'email' => 'default@example.com',
            'telephone' => '123456789',
            'customTextfield' => 'Some custom text'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $expectedResponse = [
            'errors' => [
                ['status' => 400, 'errorMessage' => 'processId should be a positive 32-bit integer.'],
                ['status' => 400, 'errorMessage' => 'authKey should be a non-empty string.'],
                ['status' => 400, 'errorMessage' => 'familyName should be a non-empty string.']
            ],
            'status' => 400
        ];

        $this->assertEquals($expectedResponse['status'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidProcessid_InvalidAuthkey_ValidFamilyname_InvalidEmail_InvalidTelephone_InvalidCustomtextfield()
    {
        $parameters = [
            'processId' => null,
            'authKey' => '',
            'familyName' => 'Smith',
            'email' => 'invalid-email',
            'telephone' => '123',
            'customTextfield' => 123
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $expectedResponse = [
            'errors' => [
                ['status' => 400, 'errorMessage' => 'processId should be a positive 32-bit integer.'],
                ['status' => 400, 'errorMessage' => 'authKey should be a non-empty string.'],
                ['status' => 400, 'errorMessage' => 'email should be a valid email address.'],
                ['status' => 400, 'errorMessage' => 'telephone should be a numeric string between 7 and 15 digits.'],
                ['status' => 400, 'errorMessage' => 'customTextfield should be a string.']
            ],
            'status' => 400
        ];

        $this->assertEquals($expectedResponse['status'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidProcessid_InvalidAuthkey_ValidFamilyname_InvalidEmail_InvalidTelephone_ValidCustomtextfield()
    {
        $parameters = [
            'processId' => null,
            'authKey' => '',
            'familyName' => 'Smith',
            'email' => 'invalid-email',
            'telephone' => '123',
            'customTextfield' => 'Some custom text'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $expectedResponse = [
            'errors' => [
                ['status' => 400, 'errorMessage' => 'processId should be a positive 32-bit integer.'],
                ['status' => 400, 'errorMessage' => 'authKey should be a non-empty string.'],
                ['status' => 400, 'errorMessage' => 'email should be a valid email address.'],
                ['status' => 400, 'errorMessage' => 'telephone should be a numeric string between 7 and 15 digits.']
            ],
            'status' => 400
        ];

        $this->assertEquals($expectedResponse['status'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidProcessid_InvalidAuthkey_ValidFamilyname_InvalidEmail_ValidTelephone_InvalidCustomtextfield()
    {
        $parameters = [
            'processId' => null,
            'authKey' => '',
            'familyName' => 'Smith',
            'email' => 'invalid-email',
            'telephone' => '123456789',
            'customTextfield' => 123
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $expectedResponse = [
            'errors' => [
                ['status' => 400, 'errorMessage' => 'processId should be a positive 32-bit integer.'],
                ['status' => 400, 'errorMessage' => 'authKey should be a non-empty string.'],
                ['status' => 400, 'errorMessage' => 'email should be a valid email address.'],
                ['status' => 400, 'errorMessage' => 'customTextfield should be a string.']
            ],
            'status' => 400
        ];

        $this->assertEquals($expectedResponse['status'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidProcessid_InvalidAuthkey_ValidFamilyname_InvalidEmail_ValidTelephone_ValidCustomtextfield()
    {
        $parameters = [
            'processId' => null,
            'authKey' => '',
            'familyName' => 'Smith',
            'email' => 'invalid-email',
            'telephone' => '123456789',
            'customTextfield' => 'Some custom text'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $expectedResponse = [
            'errors' => [
                ['status' => 400, 'errorMessage' => 'processId should be a positive 32-bit integer.'],
                ['status' => 400, 'errorMessage' => 'authKey should be a non-empty string.'],
                ['status' => 400, 'errorMessage' => 'email should be a valid email address.']
            ],
            'status' => 400
        ];

        $this->assertEquals($expectedResponse['status'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidProcessid_InvalidAuthkey_ValidFamilyname_ValidEmail_InvalidTelephone_InvalidCustomtextfield()
    {
        $parameters = [
            'processId' => null,
            'authKey' => '',
            'familyName' => 'Smith',
            'email' => 'default@example.com',
            'telephone' => '123',
            'customTextfield' => 123
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $expectedResponse = [
            'errors' => [
                ['status' => 400, 'errorMessage' => 'processId should be a positive 32-bit integer.'],
                ['status' => 400, 'errorMessage' => 'authKey should be a non-empty string.'],
                ['status' => 400, 'errorMessage' => 'telephone should be a numeric string between 7 and 15 digits.'],
                ['status' => 400, 'errorMessage' => 'customTextfield should be a string.']
            ],
            'status' => 400
        ];

        $this->assertEquals($expectedResponse['status'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidProcessid_InvalidAuthkey_ValidFamilyname_ValidEmail_InvalidTelephone_ValidCustomtextfield()
    {
        $parameters = [
            'processId' => null,
            'authKey' => '',
            'familyName' => 'Smith',
            'email' => 'default@example.com',
            'telephone' => '123',
            'customTextfield' => 'Some custom text'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $expectedResponse = [
            'errors' => [
                ['status' => 400, 'errorMessage' => 'processId should be a positive 32-bit integer.'],
                ['status' => 400, 'errorMessage' => 'authKey should be a non-empty string.'],
                ['status' => 400, 'errorMessage' => 'telephone should be a numeric string between 7 and 15 digits.']
            ],
            'status' => 400
        ];

        $this->assertEquals($expectedResponse['status'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidProcessid_InvalidAuthkey_ValidFamilyname_ValidEmail_ValidTelephone_InvalidCustomtextfield()
    {
        $parameters = [
            'processId' => null,
            'authKey' => '',
            'familyName' => 'Smith',
            'email' => 'default@example.com',
            'telephone' => '123456789',
            'customTextfield' => 123
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $expectedResponse = [
            'errors' => [
                ['status' => 400, 'errorMessage' => 'processId should be a positive 32-bit integer.'],
                ['status' => 400, 'errorMessage' => 'authKey should be a non-empty string.'],
                ['status' => 400, 'errorMessage' => 'customTextfield should be a string.']
            ],
            'status' => 400
        ];

        $this->assertEquals($expectedResponse['status'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidProcessid_InvalidAuthkey_ValidFamilyname_ValidEmail_ValidTelephone_ValidCustomtextfield()
    {
        $parameters = [
            'processId' => null,
            'authKey' => '',
            'familyName' => 'Smith',
            'email' => 'default@example.com',
            'telephone' => '123456789',
            'customTextfield' => 'Some custom text'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $expectedResponse = [
            'errors' => [
                ['status' => 400, 'errorMessage' => 'processId should be a positive 32-bit integer.'],
                ['status' => 400, 'errorMessage' => 'authKey should be a non-empty string.']
            ],
            'status' => 400
        ];

        $this->assertEquals($expectedResponse['status'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidProcessid_ValidAuthkey_InvalidFamilyname_InvalidEmail_InvalidTelephone_InvalidCustomtextfield()
    {
        $parameters = [
            'processId' => null,
            'authKey' => 'fb43',
            'familyName' => '',
            'email' => 'invalid-email',
            'telephone' => '123',
            'customTextfield' => 123
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $expectedResponse = [
            'errors' => [
                ['status' => 400, 'errorMessage' => 'processId should be a positive 32-bit integer.'],
                ['status' => 400, 'errorMessage' => 'familyName should be a non-empty string.'],
                ['status' => 400, 'errorMessage' => 'email should be a valid email address.'],
                ['status' => 400, 'errorMessage' => 'telephone should be a numeric string between 7 and 15 digits.'],
                ['status' => 400, 'errorMessage' => 'customTextfield should be a string.']
            ],
            'status' => 400
        ];

        $this->assertEquals($expectedResponse['status'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidProcessid_ValidAuthkey_InvalidFamilyname_InvalidEmail_InvalidTelephone_ValidCustomtextfield()
    {
        $parameters = [
            'processId' => null,
            'authKey' => 'fb43',
            'familyName' => '',
            'email' => 'invalid-email',
            'telephone' => '123',
            'customTextfield' => 'Some custom text'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $expectedResponse = [
            'errors' => [
                ['status' => 400, 'errorMessage' => 'processId should be a positive 32-bit integer.'],
                ['status' => 400, 'errorMessage' => 'familyName should be a non-empty string.'],
                ['status' => 400, 'errorMessage' => 'email should be a valid email address.'],
                ['status' => 400, 'errorMessage' => 'telephone should be a numeric string between 7 and 15 digits.']
            ],
            'status' => 400
        ];

        $this->assertEquals($expectedResponse['status'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidProcessid_ValidAuthkey_InvalidFamilyname_InvalidEmail_ValidTelephone_InvalidCustomtextfield()
    {
        $parameters = [
            'processId' => null,
            'authKey' => 'fb43',
            'familyName' => '',
            'email' => 'invalid-email',
            'telephone' => '123456789',
            'customTextfield' => 123
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $expectedResponse = [
            'errors' => [
                ['status' => 400, 'errorMessage' => 'processId should be a positive 32-bit integer.'],
                ['status' => 400, 'errorMessage' => 'familyName should be a non-empty string.'],
                ['status' => 400, 'errorMessage' => 'email should be a valid email address.'],
                ['status' => 400, 'errorMessage' => 'customTextfield should be a string.']
            ],
            'status' => 400
        ];

        $this->assertEquals($expectedResponse['status'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidProcessid_ValidAuthkey_InvalidFamilyname_InvalidEmail_ValidTelephone_ValidCustomtextfield()
    {
        $parameters = [
            'processId' => null,
            'authKey' => 'fb43',
            'familyName' => '',
            'email' => 'invalid-email',
            'telephone' => '123456789',
            'customTextfield' => 'Some custom text'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $expectedResponse = [
            'errors' => [
                ['status' => 400, 'errorMessage' => 'processId should be a positive 32-bit integer.'],
                ['status' => 400, 'errorMessage' => 'familyName should be a non-empty string.'],
                ['status' => 400, 'errorMessage' => 'email should be a valid email address.']
            ],
            'status' => 400
        ];

        $this->assertEquals($expectedResponse['status'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidProcessid_ValidAuthkey_InvalidFamilyname_ValidEmail_InvalidTelephone_InvalidCustomtextfield()
    {
        $parameters = [
            'processId' => null,
            'authKey' => 'fb43',
            'familyName' => '',
            'email' => 'default@example.com',
            'telephone' => '123',
            'customTextfield' => 123
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $expectedResponse = [
            'errors' => [
                ['status' => 400, 'errorMessage' => 'processId should be a positive 32-bit integer.'],
                ['status' => 400, 'errorMessage' => 'familyName should be a non-empty string.'],
                ['status' => 400, 'errorMessage' => 'telephone should be a numeric string between 7 and 15 digits.'],
                ['status' => 400, 'errorMessage' => 'customTextfield should be a string.']
            ],
            'status' => 400
        ];

        $this->assertEquals($expectedResponse['status'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidProcessid_ValidAuthkey_InvalidFamilyname_ValidEmail_InvalidTelephone_ValidCustomtextfield()
    {
        $parameters = [
            'processId' => null,
            'authKey' => 'fb43',
            'familyName' => '',
            'email' => 'default@example.com',
            'telephone' => '123',
            'customTextfield' => 'Some custom text'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $expectedResponse = [
            'errors' => [
                ['status' => 400, 'errorMessage' => 'processId should be a positive 32-bit integer.'],
                ['status' => 400, 'errorMessage' => 'familyName should be a non-empty string.'],
                ['status' => 400, 'errorMessage' => 'telephone should be a numeric string between 7 and 15 digits.']
            ],
            'status' => 400
        ];

        $this->assertEquals($expectedResponse['status'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidProcessid_ValidAuthkey_InvalidFamilyname_ValidEmail_ValidTelephone_InvalidCustomtextfield()
    {
        $parameters = [
            'processId' => null,
            'authKey' => 'fb43',
            'familyName' => '',
            'email' => 'default@example.com',
            'telephone' => '123456789',
            'customTextfield' => 123
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $expectedResponse = [
            'errors' => [
                ['status' => 400, 'errorMessage' => 'processId should be a positive 32-bit integer.'],
                ['status' => 400, 'errorMessage' => 'familyName should be a non-empty string.'],
                ['status' => 400, 'errorMessage' => 'customTextfield should be a string.']
            ],
            'status' => 400
        ];

        $this->assertEquals($expectedResponse['status'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidProcessid_ValidAuthkey_InvalidFamilyname_ValidEmail_ValidTelephone_ValidCustomtextfield()
    {
        $parameters = [
            'processId' => null,
            'authKey' => 'fb43',
            'familyName' => '',
            'email' => 'default@example.com',
            'telephone' => '123456789',
            'customTextfield' => 'Some custom text'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $expectedResponse = [
            'errors' => [
                ['status' => 400, 'errorMessage' => 'processId should be a positive 32-bit integer.'],
                ['status' => 400, 'errorMessage' => 'familyName should be a non-empty string.']
            ],
            'status' => 400
        ];

        $this->assertEquals($expectedResponse['status'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidProcessid_ValidAuthkey_ValidFamilyname_InvalidEmail_InvalidTelephone_InvalidCustomtextfield()
    {
        $parameters = [
            'processId' => null,
            'authKey' => 'fb43',
            'familyName' => 'Smith',
            'email' => 'invalid-email',
            'telephone' => '123',
            'customTextfield' => 123
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $expectedResponse = [
            'errors' => [
                ['status' => 400, 'errorMessage' => 'processId should be a positive 32-bit integer.'],
                ['status' => 400, 'errorMessage' => 'email should be a valid email address.'],
                ['status' => 400, 'errorMessage' => 'telephone should be a numeric string between 7 and 15 digits.'],
                ['status' => 400, 'errorMessage' => 'customTextfield should be a string.']
            ],
            'status' => 400
        ];

        $this->assertEquals($expectedResponse['status'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidProcessid_ValidAuthkey_ValidFamilyname_InvalidEmail_InvalidTelephone_ValidCustomtextfield()
    {
        $parameters = [
            'processId' => null,
            'authKey' => 'fb43',
            'familyName' => 'Smith',
            'email' => 'invalid-email',
            'telephone' => '123',
            'customTextfield' => 'Some custom text'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $expectedResponse = [
            'errors' => [
                ['status' => 400, 'errorMessage' => 'processId should be a positive 32-bit integer.'],
                ['status' => 400, 'errorMessage' => 'email should be a valid email address.'],
                ['status' => 400, 'errorMessage' => 'telephone should be a numeric string between 7 and 15 digits.']
            ],
            'status' => 400
        ];

        $this->assertEquals($expectedResponse['status'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidProcessid_ValidAuthkey_ValidFamilyname_InvalidEmail_ValidTelephone_InvalidCustomtextfield()
    {
        $parameters = [
            'processId' => null,
            'authKey' => 'fb43',
            'familyName' => 'Smith',
            'email' => 'invalid-email',
            'telephone' => '123456789',
            'customTextfield' => 123
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $expectedResponse = [
            'errors' => [
                ['status' => 400, 'errorMessage' => 'processId should be a positive 32-bit integer.'],
                ['status' => 400, 'errorMessage' => 'email should be a valid email address.'],
                ['status' => 400, 'errorMessage' => 'customTextfield should be a string.']
            ],
            'status' => 400
        ];

        $this->assertEquals($expectedResponse['status'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidProcessid_ValidAuthkey_ValidFamilyname_InvalidEmail_ValidTelephone_ValidCustomtextfield()
    {
        $parameters = [
            'processId' => null,
            'authKey' => 'fb43',
            'familyName' => 'Smith',
            'email' => 'invalid-email',
            'telephone' => '123456789',
            'customTextfield' => 'Some custom text'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $expectedResponse = [
            'errors' => [
                ['status' => 400, 'errorMessage' => 'processId should be a positive 32-bit integer.'],
                ['status' => 400, 'errorMessage' => 'email should be a valid email address.']
            ],
            'status' => 400
        ];

        $this->assertEquals($expectedResponse['status'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidProcessid_ValidAuthkey_ValidFamilyname_ValidEmail_InvalidTelephone_InvalidCustomtextfield()
    {
        $parameters = [
            'processId' => null,
            'authKey' => 'fb43',
            'familyName' => 'Smith',
            'email' => 'default@example.com',
            'telephone' => '123',
            'customTextfield' => 123
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $expectedResponse = [
            'errors' => [
                ['status' => 400, 'errorMessage' => 'processId should be a positive 32-bit integer.'],
                ['status' => 400, 'errorMessage' => 'telephone should be a numeric string between 7 and 15 digits.'],
                ['status' => 400, 'errorMessage' => 'customTextfield should be a string.']
            ],
            'status' => 400
        ];

        $this->assertEquals($expectedResponse['status'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidProcessid_ValidAuthkey_ValidFamilyname_ValidEmail_InvalidTelephone_ValidCustomtextfield()
    {
        $parameters = [
            'processId' => null,
            'authKey' => 'fb43',
            'familyName' => 'Smith',
            'email' => 'default@example.com',
            'telephone' => '123',
            'customTextfield' => 'Some custom text'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $expectedResponse = [
            'errors' => [
                ['status' => 400, 'errorMessage' => 'processId should be a positive 32-bit integer.'],
                ['status' => 400, 'errorMessage' => 'telephone should be a numeric string between 7 and 15 digits.']
            ],
            'status' => 400
        ];

        $this->assertEquals($expectedResponse['status'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidProcessid_ValidAuthkey_ValidFamilyname_ValidEmail_ValidTelephone_InvalidCustomtextfield()
    {
        $parameters = [
            'processId' => null,
            'authKey' => 'fb43',
            'familyName' => 'Smith',
            'email' => 'default@example.com',
            'telephone' => '123456789',
            'customTextfield' => 123
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $expectedResponse = [
            'errors' => [
                ['status' => 400, 'errorMessage' => 'processId should be a positive 32-bit integer.'],
                ['status' => 400, 'errorMessage' => 'customTextfield should be a string.']
            ],
            'status' => 400
        ];

        $this->assertEquals($expectedResponse['status'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidProcessid_ValidAuthkey_ValidFamilyname_ValidEmail_ValidTelephone_ValidCustomtextfield()
    {
        $parameters = [
            'processId' => null,
            'authKey' => 'fb43',
            'familyName' => 'Smith',
            'email' => 'default@example.com',
            'telephone' => '123456789',
            'customTextfield' => 'Some custom text'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $expectedResponse = [
            'errors' => [
                ['status' => 400, 'errorMessage' => 'processId should be a positive 32-bit integer.']
            ],
            'status' => 400
        ];

        $this->assertEquals($expectedResponse['status'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_InvalidAuthkey_InvalidFamilyname_InvalidEmail_InvalidTelephone_InvalidCustomtextfield()
    {
        $parameters = [
            'processId' => '101002',
            'authKey' => '',
            'familyName' => '',
            'email' => 'invalid-email',
            'telephone' => '123',
            'customTextfield' => 123
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $expectedResponse = [
            'errors' => [
                ['status' => 400, 'errorMessage' => 'authKey should be a non-empty string.'],
                ['status' => 400, 'errorMessage' => 'familyName should be a non-empty string.'],
                ['status' => 400, 'errorMessage' => 'email should be a valid email address.'],
                ['status' => 400, 'errorMessage' => 'telephone should be a numeric string between 7 and 15 digits.'],
                ['status' => 400, 'errorMessage' => 'customTextfield should be a string.']
            ],
            'status' => 400
        ];

        $this->assertEquals($expectedResponse['status'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_InvalidAuthkey_InvalidFamilyname_InvalidEmail_InvalidTelephone_ValidCustomtextfield()
    {
        $parameters = [
            'processId' => '101002',
            'authKey' => '',
            'familyName' => '',
            'email' => 'invalid-email',
            'telephone' => '123',
            'customTextfield' => 'Some custom text'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $expectedResponse = [
            'errors' => [
                ['status' => 400, 'errorMessage' => 'authKey should be a non-empty string.'],
                ['status' => 400, 'errorMessage' => 'familyName should be a non-empty string.'],
                ['status' => 400, 'errorMessage' => 'email should be a valid email address.'],
                ['status' => 400, 'errorMessage' => 'telephone should be a numeric string between 7 and 15 digits.']
            ],
            'status' => 400
        ];

        $this->assertEquals($expectedResponse['status'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_InvalidAuthkey_InvalidFamilyname_InvalidEmail_ValidTelephone_InvalidCustomtextfield()
    {
        $parameters = [
            'processId' => '101002',
            'authKey' => '',
            'familyName' => '',
            'email' => 'invalid-email',
            'telephone' => '123456789',
            'customTextfield' => 123
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $expectedResponse = [
            'errors' => [
                ['status' => 400, 'errorMessage' => 'authKey should be a non-empty string.'],
                ['status' => 400, 'errorMessage' => 'familyName should be a non-empty string.'],
                ['status' => 400, 'errorMessage' => 'email should be a valid email address.'],
                ['status' => 400, 'errorMessage' => 'customTextfield should be a string.']
            ],
            'status' => 400
        ];

        $this->assertEquals($expectedResponse['status'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_InvalidAuthkey_InvalidFamilyname_InvalidEmail_ValidTelephone_ValidCustomtextfield()
    {
        $parameters = [
            'processId' => '101002',
            'authKey' => '',
            'familyName' => '',
            'email' => 'invalid-email',
            'telephone' => '123456789',
            'customTextfield' => 'Some custom text'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $expectedResponse = [
            'errors' => [
                ['status' => 400, 'errorMessage' => 'authKey should be a non-empty string.'],
                ['status' => 400, 'errorMessage' => 'familyName should be a non-empty string.'],
                ['status' => 400, 'errorMessage' => 'email should be a valid email address.']
            ],
            'status' => 400
        ];

        $this->assertEquals($expectedResponse['status'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_InvalidAuthkey_InvalidFamilyname_ValidEmail_InvalidTelephone_InvalidCustomtextfield()
    {
        $parameters = [
            'processId' => '101002',
            'authKey' => '',
            'familyName' => '',
            'email' => 'default@example.com',
            'telephone' => '123',
            'customTextfield' => 123
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $expectedResponse = [
            'errors' => [
                ['status' => 400, 'errorMessage' => 'authKey should be a non-empty string.'],
                ['status' => 400, 'errorMessage' => 'familyName should be a non-empty string.'],
                ['status' => 400, 'errorMessage' => 'telephone should be a numeric string between 7 and 15 digits.'],
                ['status' => 400, 'errorMessage' => 'customTextfield should be a string.']
            ],
            'status' => 400
        ];

        $this->assertEquals($expectedResponse['status'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_InvalidAuthkey_InvalidFamilyname_ValidEmail_InvalidTelephone_ValidCustomtextfield()
    {
        $parameters = [
            'processId' => '101002',
            'authKey' => '',
            'familyName' => '',
            'email' => 'default@example.com',
            'telephone' => '123',
            'customTextfield' => 'Some custom text'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $expectedResponse = [
            'errors' => [
                ['status' => 400, 'errorMessage' => 'authKey should be a non-empty string.'],
                ['status' => 400, 'errorMessage' => 'familyName should be a non-empty string.'],
                ['status' => 400, 'errorMessage' => 'telephone should be a numeric string between 7 and 15 digits.']
            ],
            'status' => 400
        ];

        $this->assertEquals($expectedResponse['status'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_InvalidAuthkey_InvalidFamilyname_ValidEmail_ValidTelephone_InvalidCustomtextfield()
    {
        $parameters = [
            'processId' => '101002',
            'authKey' => '',
            'familyName' => '',
            'email' => 'default@example.com',
            'telephone' => '123456789',
            'customTextfield' => 123
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $expectedResponse = [
            'errors' => [
                ['status' => 400, 'errorMessage' => 'authKey should be a non-empty string.'],
                ['status' => 400, 'errorMessage' => 'familyName should be a non-empty string.'],
                ['status' => 400, 'errorMessage' => 'customTextfield should be a string.']
            ],
            'status' => 400
        ];

        $this->assertEquals($expectedResponse['status'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_InvalidAuthkey_InvalidFamilyname_ValidEmail_ValidTelephone_ValidCustomtextfield()
    {
        $parameters = [
            'processId' => '101002',
            'authKey' => '',
            'familyName' => '',
            'email' => 'default@example.com',
            'telephone' => '123456789',
            'customTextfield' => 'Some custom text'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $expectedResponse = [
            'errors' => [
                ['status' => 400, 'errorMessage' => 'authKey should be a non-empty string.'],
                ['status' => 400, 'errorMessage' => 'familyName should be a non-empty string.']
            ],
            'status' => 400
        ];

        $this->assertEquals($expectedResponse['status'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_InvalidAuthkey_ValidFamilyname_InvalidEmail_InvalidTelephone_InvalidCustomtextfield()
    {
        $parameters = [
            'processId' => '101002',
            'authKey' => '',
            'familyName' => 'Smith',
            'email' => 'invalid-email',
            'telephone' => '123',
            'customTextfield' => 123
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $expectedResponse = [
            'errors' => [
                ['status' => 400, 'errorMessage' => 'authKey should be a non-empty string.'],
                ['status' => 400, 'errorMessage' => 'email should be a valid email address.'],
                ['status' => 400, 'errorMessage' => 'telephone should be a numeric string between 7 and 15 digits.'],
                ['status' => 400, 'errorMessage' => 'customTextfield should be a string.']
            ],
            'status' => 400
        ];

        $this->assertEquals($expectedResponse['status'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_InvalidAuthkey_ValidFamilyname_InvalidEmail_InvalidTelephone_ValidCustomtextfield()
    {
        $parameters = [
            'processId' => '101002',
            'authKey' => '',
            'familyName' => 'Smith',
            'email' => 'invalid-email',
            'telephone' => '123',
            'customTextfield' => 'Some custom text'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $expectedResponse = [
            'errors' => [
                ['status' => 400, 'errorMessage' => 'authKey should be a non-empty string.'],
                ['status' => 400, 'errorMessage' => 'email should be a valid email address.'],
                ['status' => 400, 'errorMessage' => 'telephone should be a numeric string between 7 and 15 digits.']
            ],
            'status' => 400
        ];

        $this->assertEquals($expectedResponse['status'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_InvalidAuthkey_ValidFamilyname_InvalidEmail_ValidTelephone_InvalidCustomtextfield()
    {
        $parameters = [
            'processId' => '101002',
            'authKey' => '',
            'familyName' => 'Smith',
            'email' => 'invalid-email',
            'telephone' => '123456789',
            'customTextfield' => 123
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $expectedResponse = [
            'errors' => [
                ['status' => 400, 'errorMessage' => 'authKey should be a non-empty string.'],
                ['status' => 400, 'errorMessage' => 'email should be a valid email address.'],
                ['status' => 400, 'errorMessage' => 'customTextfield should be a string.']
            ],
            'status' => 400
        ];

        $this->assertEquals($expectedResponse['status'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_InvalidAuthkey_ValidFamilyname_InvalidEmail_ValidTelephone_ValidCustomtextfield()
    {
        $parameters = [
            'processId' => '101002',
            'authKey' => '',
            'familyName' => 'Smith',
            'email' => 'invalid-email',
            'telephone' => '123456789',
            'customTextfield' => 'Some custom text'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $expectedResponse = [
            'errors' => [
                ['status' => 400, 'errorMessage' => 'authKey should be a non-empty string.'],
                ['status' => 400, 'errorMessage' => 'email should be a valid email address.']
            ],
            'status' => 400
        ];

        $this->assertEquals($expectedResponse['status'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_InvalidAuthkey_ValidFamilyname_ValidEmail_InvalidTelephone_InvalidCustomtextfield()
    {
        $parameters = [
            'processId' => '101002',
            'authKey' => '',
            'familyName' => 'Smith',
            'email' => 'default@example.com',
            'telephone' => '123',
            'customTextfield' => 123
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $expectedResponse = [
            'errors' => [
                ['status' => 400, 'errorMessage' => 'authKey should be a non-empty string.'],
                ['status' => 400, 'errorMessage' => 'telephone should be a numeric string between 7 and 15 digits.'],
                ['status' => 400, 'errorMessage' => 'customTextfield should be a string.']
            ],
            'status' => 400
        ];

        $this->assertEquals($expectedResponse['status'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_InvalidAuthkey_ValidFamilyname_ValidEmail_InvalidTelephone_ValidCustomtextfield()
    {
        $parameters = [
            'processId' => '101002',
            'authKey' => '',
            'familyName' => 'Smith',
            'email' => 'default@example.com',
            'telephone' => '123',
            'customTextfield' => 'Some custom text'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $expectedResponse = [
            'errors' => [
                ['status' => 400, 'errorMessage' => 'authKey should be a non-empty string.'],
                ['status' => 400, 'errorMessage' => 'telephone should be a numeric string between 7 and 15 digits.']
            ],
            'status' => 400
        ];

        $this->assertEquals($expectedResponse['status'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_InvalidAuthkey_ValidFamilyname_ValidEmail_ValidTelephone_InvalidCustomtextfield()
    {
        $parameters = [
            'processId' => '101002',
            'authKey' => '',
            'familyName' => 'Smith',
            'email' => 'default@example.com',
            'telephone' => '123456789',
            'customTextfield' => 123
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $expectedResponse = [
            'errors' => [
                ['status' => 400, 'errorMessage' => 'authKey should be a non-empty string.'],
                ['status' => 400, 'errorMessage' => 'customTextfield should be a string.']
            ],
            'status' => 400
        ];

        $this->assertEquals($expectedResponse['status'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_InvalidAuthkey_ValidFamilyname_ValidEmail_ValidTelephone_ValidCustomtextfield()
    {
        $parameters = [
            'processId' => '101002',
            'authKey' => '',
            'familyName' => 'Smith',
            'email' => 'default@example.com',
            'telephone' => '123456789',
            'customTextfield' => 'Some custom text'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $expectedResponse = [
            'errors' => [
                ['status' => 400, 'errorMessage' => 'authKey should be a non-empty string.']
            ],
            'status' => 400
        ];

        $this->assertEquals($expectedResponse['status'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_ValidAuthkey_InvalidFamilyname_InvalidEmail_InvalidTelephone_InvalidCustomtextfield()
    {
        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43',
            'familyName' => '',
            'email' => 'invalid-email',
            'telephone' => '123',
            'customTextfield' => 123
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $expectedResponse = [
            'errors' => [
                ['status' => 400, 'errorMessage' => 'familyName should be a non-empty string.'],
                ['status' => 400, 'errorMessage' => 'email should be a valid email address.'],
                ['status' => 400, 'errorMessage' => 'telephone should be a numeric string between 7 and 15 digits.'],
                ['status' => 400, 'errorMessage' => 'customTextfield should be a string.']
            ],
            'status' => 400
        ];

        $this->assertEquals($expectedResponse['status'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_ValidAuthkey_InvalidFamilyname_InvalidEmail_InvalidTelephone_ValidCustomtextfield()
    {
        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43',
            'familyName' => '',
            'email' => 'invalid-email',
            'telephone' => '123',
            'customTextfield' => 'Some custom text'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $expectedResponse = [
            'errors' => [
                ['status' => 400, 'errorMessage' => 'familyName should be a non-empty string.'],
                ['status' => 400, 'errorMessage' => 'email should be a valid email address.'],
                ['status' => 400, 'errorMessage' => 'telephone should be a numeric string between 7 and 15 digits.']
            ],
            'status' => 400
        ];

        $this->assertEquals($expectedResponse['status'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_ValidAuthkey_InvalidFamilyname_InvalidEmail_ValidTelephone_InvalidCustomtextfield()
    {
        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43',
            'familyName' => '',
            'email' => 'invalid-email',
            'telephone' => '123456789',
            'customTextfield' => 123
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $expectedResponse = [
            'errors' => [
                ['status' => 400, 'errorMessage' => 'familyName should be a non-empty string.'],
                ['status' => 400, 'errorMessage' => 'email should be a valid email address.'],
                ['status' => 400, 'errorMessage' => 'customTextfield should be a string.']
            ],
            'status' => 400
        ];

        $this->assertEquals($expectedResponse['status'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_ValidAuthkey_InvalidFamilyname_InvalidEmail_ValidTelephone_ValidCustomtextfield()
    {
        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43',
            'familyName' => '',
            'email' => 'invalid-email',
            'telephone' => '123456789',
            'customTextfield' => 'Some custom text'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $expectedResponse = [
            'errors' => [
                ['status' => 400, 'errorMessage' => 'familyName should be a non-empty string.'],
                ['status' => 400, 'errorMessage' => 'email should be a valid email address.']
            ],
            'status' => 400
        ];

        $this->assertEquals($expectedResponse['status'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_ValidAuthkey_InvalidFamilyname_ValidEmail_InvalidTelephone_InvalidCustomtextfield()
    {
        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43',
            'familyName' => '',
            'email' => 'default@example.com',
            'telephone' => '123',
            'customTextfield' => 123
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $expectedResponse = [
            'errors' => [
                ['status' => 400, 'errorMessage' => 'familyName should be a non-empty string.'],
                ['status' => 400, 'errorMessage' => 'telephone should be a numeric string between 7 and 15 digits.'],
                ['status' => 400, 'errorMessage' => 'customTextfield should be a string.']
            ],
            'status' => 400
        ];

        $this->assertEquals($expectedResponse['status'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_ValidAuthkey_InvalidFamilyname_ValidEmail_InvalidTelephone_ValidCustomtextfield()
    {
        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43',
            'familyName' => '',
            'email' => 'default@example.com',
            'telephone' => '123',
            'customTextfield' => 'Some custom text'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $expectedResponse = [
            'errors' => [
                ['status' => 400, 'errorMessage' => 'familyName should be a non-empty string.'],
                ['status' => 400, 'errorMessage' => 'telephone should be a numeric string between 7 and 15 digits.']
            ],
            'status' => 400
        ];

        $this->assertEquals($expectedResponse['status'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_ValidAuthkey_InvalidFamilyname_ValidEmail_ValidTelephone_InvalidCustomtextfield()
    {
        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43',
            'familyName' => '',
            'email' => 'default@example.com',
            'telephone' => '123456789',
            'customTextfield' => 123
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $expectedResponse = [
            'errors' => [
                ['status' => 400, 'errorMessage' => 'familyName should be a non-empty string.'],
                ['status' => 400, 'errorMessage' => 'customTextfield should be a string.']
            ],
            'status' => 400
        ];

        $this->assertEquals($expectedResponse['status'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_ValidAuthkey_InvalidFamilyname_ValidEmail_ValidTelephone_ValidCustomtextfield()
    {
        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43',
            'familyName' => '',
            'email' => 'default@example.com',
            'telephone' => '123456789',
            'customTextfield' => 'Some custom text'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $expectedResponse = [
            'errors' => [
                ['status' => 400, 'errorMessage' => 'familyName should be a non-empty string.']
            ],
            'status' => 400
        ];

        $this->assertEquals($expectedResponse['status'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_ValidAuthkey_ValidFamilyname_InvalidEmail_InvalidTelephone_InvalidCustomtextfield()
    {
        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43',
            'familyName' => 'Smith',
            'email' => 'invalid-email',
            'telephone' => '123',
            'customTextfield' => 123
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $expectedResponse = [
            'errors' => [
                ['status' => 400, 'errorMessage' => 'email should be a valid email address.'],
                ['status' => 400, 'errorMessage' => 'telephone should be a numeric string between 7 and 15 digits.'],
                ['status' => 400, 'errorMessage' => 'customTextfield should be a string.']
            ],
            'status' => 400
        ];

        $this->assertEquals($expectedResponse['status'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_ValidAuthkey_ValidFamilyname_InvalidEmail_InvalidTelephone_ValidCustomtextfield()
    {
        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43',
            'familyName' => 'Smith',
            'email' => 'invalid-email',
            'telephone' => '123',
            'customTextfield' => 'Some custom text'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $expectedResponse = [
            'errors' => [
                ['status' => 400, 'errorMessage' => 'email should be a valid email address.'],
                ['status' => 400, 'errorMessage' => 'telephone should be a numeric string between 7 and 15 digits.']
            ],
            'status' => 400
        ];

        $this->assertEquals($expectedResponse['status'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_ValidAuthkey_ValidFamilyname_InvalidEmail_ValidTelephone_InvalidCustomtextfield()
    {
        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43',
            'familyName' => 'Smith',
            'email' => 'invalid-email',
            'telephone' => '123456789',
            'customTextfield' => 123
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $expectedResponse = [
            'errors' => [
                ['status' => 400, 'errorMessage' => 'email should be a valid email address.'],
                ['status' => 400, 'errorMessage' => 'customTextfield should be a string.']
            ],
            'status' => 400
        ];

        $this->assertEquals($expectedResponse['status'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_ValidAuthkey_ValidFamilyname_InvalidEmail_ValidTelephone_ValidCustomtextfield()
    {
        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43',
            'familyName' => 'Smith',
            'email' => 'invalid-email',
            'telephone' => '123456789',
            'customTextfield' => 'Some custom text'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $expectedResponse = [
            'errors' => [
                ['status' => 400, 'errorMessage' => 'email should be a valid email address.']
            ],
            'status' => 400
        ];

        $this->assertEquals($expectedResponse['status'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_ValidAuthkey_ValidFamilyname_ValidEmail_InvalidTelephone_InvalidCustomtextfield()
    {
        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43',
            'familyName' => 'Smith',
            'email' => 'default@example.com',
            'telephone' => '123',
            'customTextfield' => 123
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $expectedResponse = [
            'errors' => [
                ['status' => 400, 'errorMessage' => 'telephone should be a numeric string between 7 and 15 digits.'],
                ['status' => 400, 'errorMessage' => 'customTextfield should be a string.']
            ],
            'status' => 400
        ];

        $this->assertEquals($expectedResponse['status'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_ValidAuthkey_ValidFamilyname_ValidEmail_InvalidTelephone_ValidCustomtextfield()
    {
        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43',
            'familyName' => 'Smith',
            'email' => 'default@example.com',
            'telephone' => '123',
            'customTextfield' => 'Some custom text'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $expectedResponse = [
            'errors' => [
                ['status' => 400, 'errorMessage' => 'telephone should be a numeric string between 7 and 15 digits.']
            ],
            'status' => 400
        ];

        $this->assertEquals($expectedResponse['status'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_ValidAuthkey_ValidFamilyname_ValidEmail_ValidTelephone_InvalidCustomtextfield()
    {
        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43',
            'familyName' => 'Smith',
            'email' => 'default@example.com',
            'telephone' => '123456789',
            'customTextfield' => 123
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $expectedResponse = [
            'errors' => [
                ['status' => 400, 'errorMessage' => 'customTextfield should be a string.']
            ],
            'status' => 400
        ];

        $this->assertEquals($expectedResponse['status'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

}
