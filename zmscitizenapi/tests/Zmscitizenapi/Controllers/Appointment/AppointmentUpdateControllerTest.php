<?php

namespace BO\Zmscitizenapi\Tests\Controllers\Appointment;

use BO\Zmscitizenapi\Utils\ErrorMessages;
use BO\Zmscitizenapi\Tests\ControllerTestCase;

class AppointmentUpdateControllerTest extends ControllerTestCase
{

    protected $classname = "\BO\Zmscitizenapi\Controllers\Appointment\AppointmentUpdateController";

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
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/merged-mailtemplates/102522/',
                    'response' => $this->readFixture("GET_merged_mailtemplates.json")
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
            'familyName' => 'TEST_USER',
            'email' => "test@muenchen.de",
            'telephone' => '123456789',
            'customTextfield' => "Some custom text",
            'customTextfield2' => "Another custom text",
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $this->assertArrayHasKey('captchaToken', $responseBody);
        $this->assertIsString($responseBody['captchaToken']);
        unset($responseBody['captchaToken']);

        $expectedResponse = [
            "processId" => 101002,
            "timestamp" => "1727865900",
            "authKey" => "fb43",
            "familyName" => "TEST_USER",
            "customTextfield" => "Some custom text",
            'customTextfield2' => "Another custom text",
            "email" => "test@muenchen.de",
            "telephone" => "123456789",
            "officeName" => null,
            "officeId" => 0,
            "scope" => [
                "id" => 0,
                'provider' => [
                    'contact'=> null,
                    'id'=> 0,
                    'lat'=> null,
                    'lon'=> null,
                    'name'=> '',
                    'displayame'=> '',
                    'source'=> 'dldb'
                ],
                "shortName" => '',
                "emailFrom" => '',
                'emailRequired' => null,
                "telephoneActivated" => null,
                "telephoneRequired" => null,
                "customTextfieldActivated" => null,
                "customTextfieldRequired" => null,
                "customTextfieldLabel" => null,
                "customTextfield2Activated" => null,
                "customTextfield2Required" => null,
                "customTextfield2Label" => null,
                "captchaActivatedRequired" => null,
                "infoForAppointment" => null,
                "infoForAllAppointments" => null,
                "slotsPerAppointment" => null,
                "appointmentsPerMail" => null,
                "whitelistedMails" => null,
                "reservationDuration" => null,
                "activationDuration" => null,
                "hint" => null
            ],
            "status" => "reserved",
            "subRequestCounts" => [],
            "serviceId" => 10242339,
            "serviceName" => "Adressänderung Personalausweis, Reisepass, eAT",
            "serviceCount" => 1,
            "slotCount" => 1,
        ];

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertArrayHasKey('icsContent', $responseBody);
        $this->assertStringContainsString('BEGIN:VCALENDAR', $responseBody['icsContent']);
        unset($responseBody['icsContent']);
        unset($expectedResponse['icsContent']);
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidProcessid_ValidAuthkey_ValidFamilyname_ValidEmail_ValidTelephone_ValidCustomtextfield()
    {
        $parameters = [
            'processId' => null,
            'authKey' => 'fb43',
            'familyName' => 'TEST_USER',
            'email' => 'test@muenchen.de',
            'telephone' => '123456789',
            'customTextfield' => 'Some custom text'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidProcessId')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidProcessId')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_ValidAuthkey_InvalidFamilyname_InvalidEmail_InvalidTelephone_InvalidCustomtextfield()
    {
        $this->setApiCalls([
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
            ],
            [
                'function' => 'readGetResult',
                'url' => '/merged-mailtemplates/102522/',
                'response' => $this->readFixture("GET_merged_mailtemplates.json")
            ]
        ]);
        
        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43',
            'familyName' => '',
            'email' => 'invalid-email',
            'telephone' => '123',
            'customTextfield' => ''
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidFamilyName'),
                ErrorMessages::get('invalidEmail'),
                ErrorMessages::get('invalidTelephone'),
                ErrorMessages::get('invalidCustomTextfield')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidFamilyName')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidEmail')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidTelephone')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidCustomTextfield')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_ValidAuthkey_InvalidFamilyname_InvalidEmail_InvalidTelephone_ValidCustomtextfield()
    {
        $this->setApiCalls([
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
            ],
            [
                'function' => 'readGetResult',
                'url' => '/merged-mailtemplates/102522/',
                'response' => $this->readFixture("GET_merged_mailtemplates.json")
            ]
        ]);

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
                ErrorMessages::get('invalidFamilyName'),
                ErrorMessages::get('invalidEmail'),
                ErrorMessages::get('invalidTelephone')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidFamilyName')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidEmail')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidTelephone')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_ValidAuthkey_InvalidFamilyname_InvalidEmail_ValidTelephone_InvalidCustomtextfield()
    {
        $this->setApiCalls([
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
            ],
            [
                'function' => 'readGetResult',
                'url' => '/merged-mailtemplates/102522/',
                'response' => $this->readFixture("GET_merged_mailtemplates.json")
            ]
        ]);

        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43',
            'familyName' => '',
            'email' => 'invalid-email',
            'telephone' => '123456789',
            'customTextfield' => ''
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidFamilyName'),
                ErrorMessages::get('invalidEmail'),
                ErrorMessages::get('invalidCustomTextfield')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidFamilyName')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidEmail')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidCustomTextfield')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_ValidAuthkey_InvalidFamilyname_InvalidEmail_ValidTelephone_ValidCustomtextfield()
    {
        $this->setApiCalls([
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
            ],
            [
                'function' => 'readGetResult',
                'url' => '/merged-mailtemplates/102522/',
                'response' => $this->readFixture("GET_merged_mailtemplates.json")
            ]
        ]);

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
                ErrorMessages::get('invalidFamilyName'),
                ErrorMessages::get('invalidEmail')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidFamilyName')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidEmail')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_ValidAuthkey_InvalidFamilyname_ValidEmail_InvalidTelephone_InvalidCustomtextfield()
    {
        $this->setApiCalls([
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
            ],
            [
                'function' => 'readGetResult',
                'url' => '/merged-mailtemplates/102522/',
                'response' => $this->readFixture("GET_merged_mailtemplates.json")
            ]
        ]);

        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43',
            'familyName' => '',
            'email' => 'test@muenchen.de',
            'telephone' => '123',
            'customTextfield' => ''
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidFamilyName'),
                ErrorMessages::get('invalidTelephone'),
                ErrorMessages::get('invalidCustomTextfield')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidFamilyName')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidTelephone')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidCustomTextfield')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_ValidAuthkey_InvalidFamilyname_ValidEmail_InvalidTelephone_ValidCustomtextfield()
    {
        $this->setApiCalls([
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
            ],
            [
                'function' => 'readGetResult',
                'url' => '/merged-mailtemplates/102522/',
                'response' => $this->readFixture("GET_merged_mailtemplates.json")
            ]
        ]);

        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43',
            'familyName' => '',
            'email' => 'test@muenchen.de',
            'telephone' => '123',
            'customTextfield' => 'Some custom text'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidFamilyName'),
                ErrorMessages::get('invalidTelephone')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidFamilyName')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidTelephone')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_ValidAuthkey_InvalidFamilyname_ValidEmail_ValidTelephone_InvalidCustomtextfield()
    {
        $this->setApiCalls([
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
            ],
            [
                'function' => 'readGetResult',
                'url' => '/merged-mailtemplates/102522/',
                'response' => $this->readFixture("GET_merged_mailtemplates.json")
            ]
        ]);

        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43',
            'familyName' => '',
            'email' => 'test@muenchen.de',
            'telephone' => '123456789',
            'customTextfield' => ''
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidFamilyName'),
                ErrorMessages::get('invalidCustomTextfield')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidFamilyName')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidCustomTextfield')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_ValidAuthkey_InvalidFamilyname_ValidEmail_ValidTelephone_ValidCustomtextfield()
    {
        $this->setApiCalls([
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
            ],
            [
                'function' => 'readGetResult',
                'url' => '/merged-mailtemplates/102522/',
                'response' => $this->readFixture("GET_merged_mailtemplates.json")
            ]
        ]);

        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43',
            'familyName' => '',
            'email' => 'test@muenchen.de',
            'telephone' => '123456789',
            'customTextfield' => 'Some custom text'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidFamilyName')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidFamilyName')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_ValidAuthkey_ValidFamilyname_InvalidEmail_InvalidTelephone_InvalidCustomtextfield()
    {
        $this->setApiCalls([
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
            ],
            [
                'function' => 'readGetResult',
                'url' => '/merged-mailtemplates/102522/',
                'response' => $this->readFixture("GET_merged_mailtemplates.json")
            ]
        ]);

        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43',
            'familyName' => 'TEST_USER',
            'email' => 'invalid-email',
            'telephone' => '123',
            'customTextfield' => ''
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidEmail'),
                ErrorMessages::get('invalidTelephone'),
                ErrorMessages::get('invalidCustomTextfield')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidEmail')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidTelephone')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidCustomTextfield')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_ValidAuthkey_ValidFamilyname_InvalidEmail_InvalidTelephone_ValidCustomtextfield()
    {
        $this->setApiCalls([
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
            ],
            [
                'function' => 'readGetResult',
                'url' => '/merged-mailtemplates/102522/',
                'response' => $this->readFixture("GET_merged_mailtemplates.json")
            ]
        ]);

        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43',
            'familyName' => 'TEST_USER',
            'email' => 'invalid-email',
            'telephone' => '123',
            'customTextfield' => 'Some custom text'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidEmail'),
                ErrorMessages::get('invalidTelephone')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidEmail')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidTelephone')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_ValidAuthkey_ValidFamilyname_InvalidEmail_ValidTelephone_InvalidCustomtextfield()
    {
        $this->setApiCalls([
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
            ],
            [
                'function' => 'readGetResult',
                'url' => '/merged-mailtemplates/102522/',
                'response' => $this->readFixture("GET_merged_mailtemplates.json")
            ]
        ]);

        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43',
            'familyName' => 'TEST_USER',
            'email' => 'invalid-email',
            'telephone' => '123456789',
            'customTextfield' => ''
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidEmail'),
                ErrorMessages::get('invalidCustomTextfield')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidEmail')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidCustomTextfield')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_ValidAuthkey_ValidFamilyname_InvalidEmail_ValidTelephone_ValidCustomtextfield()
    {
        $this->setApiCalls([
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
            ],
            [
                'function' => 'readGetResult',
                'url' => '/merged-mailtemplates/102522/',
                'response' => $this->readFixture("GET_merged_mailtemplates.json")
            ]
        ]);

        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43',
            'familyName' => 'TEST_USER',
            'email' => 'invalid-email',
            'telephone' => '123456789',
            'customTextfield' => 'Some custom text'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidEmail')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidEmail')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_ValidAuthkey_ValidFamilyname_ValidEmail_InvalidTelephone_InvalidCustomtextfield()
    {
        $this->setApiCalls([
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
            ],
            [
                'function' => 'readGetResult',
                'url' => '/merged-mailtemplates/102522/',
                'response' => $this->readFixture("GET_merged_mailtemplates.json")
            ]
        ]);

        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43',
            'familyName' => 'TEST_USER',
            'email' => 'test@muenchen.de',
            'telephone' => '123',
            'customTextfield' => ''
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidTelephone'),
                ErrorMessages::get('invalidCustomTextfield')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidTelephone')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidCustomTextfield')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_ValidAuthkey_ValidFamilyname_ValidEmail_InvalidTelephone_ValidCustomtextfield()
    {
        $this->setApiCalls([
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
            ],
            [
                'function' => 'readGetResult',
                'url' => '/merged-mailtemplates/102522/',
                'response' => $this->readFixture("GET_merged_mailtemplates.json")
            ]
        ]);

        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43',
            'familyName' => 'TEST_USER',
            'email' => 'test@muenchen.de',
            'telephone' => '123',
            'customTextfield' => 'Some custom text'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidTelephone')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidTelephone')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_ValidAuthkey_ValidFamilyname_ValidEmail_ValidTelephone_InvalidCustomtextfield()
    {
        $this->setApiCalls([
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
            ],
            [
                'function' => 'readGetResult',
                'url' => '/merged-mailtemplates/102522/',
                'response' => $this->readFixture("GET_merged_mailtemplates.json")
            ]
        ]);

        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43',
            'familyName' => 'TEST_USER',
            'email' => 'test@muenchen.de',
            'telephone' => '123456789',
            'customTextfield' => ''
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidCustomTextfield')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidCustomTextfield')['statusCode'], $response->getStatusCode());
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
                    'function' => 'readGetResult',
                    'url' => '/source/unittest/',
                    'parameters' => [
                        'resolveReferences' => 2,
                    ],
                    'response' => $this->readFixture("GET_SourceGet_dldb.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/merged-mailtemplates/102522/',
                    'response' => $this->readFixture("GET_merged_mailtemplates.json")
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
            'familyName' => 'TEST_USER',
            'email' => "test@muenchen.de",
            'telephone' => '123456789',
            'customTextfield' => "Some custom text",
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('tooManyAppointmentsWithSameMail')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('tooManyAppointmentsWithSameMail')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testAppointmentNotFoundException()
    {

        $exception = new \BO\Zmsclient\Exception();
        $exception->template = 'BO\\Zmsapi\\Exception\\Process\\ProcessNotFound';

        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/process/101003/fb43/',
                    'parameters' => [
                        'resolveReferences' => 2,
                    ],
                    'exception' => $exception
                ]
            ]
        );

        $parameters = [
            'processId' => '101003',
            'authKey' => 'fb43',
            'familyName' => 'TEST_USER',
            'email' => "test@muenchen.de",
            'telephone' => '123456789',
            'customTextfield' => "Some custom text",
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('appointmentNotFound')
            ]
        ];
        $this->assertEquals(ErrorMessages::get('appointmentNotFound')['statusCode'], $response->getStatusCode());
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
                    'url' => '/process/101003/wrongKey/',
                    'parameters' => [
                        'resolveReferences' => 2,
                    ],
                    'exception' => $exception
                ]
            ]
        );

        $parameters = [
            'processId' => '101003',
            'authKey' => 'wrongKey',
            'familyName' => 'TEST_USER',
            'email' => "test@muenchen.de",
            'telephone' => '123456789',
            'customTextfield' => "Some custom text",
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('authKeyMismatch')
            ]
        ];
        $this->assertEquals(ErrorMessages::get('authKeyMismatch')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testProcessInvalid()
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
            'familyName' => 'TEST_USER',
            'email' => 'test@muenchen.de',
            'telephone' => '123456789',
            'customTextfield' => 'Some custom text'
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

    public function testProcessNotReservedAnymore()
    {
        $exception = new \BO\Zmsclient\Exception();
        $exception->template = 'BO\\Zmsapi\\Exception\\Process\\ProcessNotReservedAnymore';
    
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
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/merged-mailtemplates/102522/',
                    'response' => $this->readFixture("GET_merged_mailtemplates.json")
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
            'familyName' => 'TEST_USER',
            'email' => 'test@muenchen.de',
            'telephone' => '123456789',
            'customTextfield' => 'Some custom text'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('processNotReservedAnymore')
            ]
        ];
        $this->assertEquals(ErrorMessages::get('processNotReservedAnymore')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testEmailRequired()
    {

        $exception = new \BO\Zmsclient\Exception();
        $exception->template = 'BO\\Zmsapi\\Exception\\Process\\EmailRequired';

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
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/merged-mailtemplates/102522/',
                    'response' => $this->readFixture("GET_merged_mailtemplates.json")
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
            'familyName' => 'TEST_USER',
            'email' => "test@muenchen.de",
            'telephone' => '123456789',
            'customTextfield' => "Some custom text",
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('emailIsRequired')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('emailIsRequired')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testTelephoneRequired()
    {

        $exception = new \BO\Zmsclient\Exception();
        $exception->template = 'BO\\Zmsapi\\Exception\\Process\\TelephoneRequired';

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
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/merged-mailtemplates/102522/',
                    'response' => $this->readFixture("GET_merged_mailtemplates.json")
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
            'familyName' => 'TEST_USER',
            'email' => "test@muenchen.de",
            'telephone' => '123456789',
            'customTextfield' => "Some custom text",
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('telephoneIsRequired')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('telephoneIsRequired')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

}
