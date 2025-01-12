<?php

namespace BO\Zmscitizenapi\Tests\Controllers\Appointment;

use BO\Zmscitizenapi\Localization\ErrorMessages;
use BO\Zmscitizenapi\Tests\ControllerTestCase;

class AppointmentUpdateTest extends ControllerTestCase
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
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            "processId" => 101002,
            "timestamp" => "1727865900",
            "authKey" => "fb43",
            "familyName" => "TEST_USER",
            "customTextfield" => "Some custom text",
            "email" => "test@muenchen.de",
            "telephone" => "123456789",
            "officeName" => null,
            "officeId" => 0,
            "scope" => [
                "id" => 0,
                'provider' => [
                    'contact'=> null,
                    'id'=> null,
                    'lat'=> null,
                    'lon'=> null,
                    'name'=> null,
                    'source'=> null
                ],
                "shortName" => null,
                "telephoneActivated" => null,
                "telephoneRequired" => null,
                "customTextfieldActivated" => null,
                "customTextfieldRequired" => null,
                "customTextfieldLabel" => null,
                "captchaActivatedRequired" => null,
                "displayInfo" => null
            ],
            "status" => "reserved",
            "subRequestCounts" => [],
            "serviceId" => 10242339,
            "serviceCount" => 1
        ];
        $this->assertEquals(200, $response->getStatusCode());
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
            'customTextfield' => ''
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidProcessId'),
                ErrorMessages::get('invalidAuthKey'),
                ErrorMessages::get('invalidFamilyName'),
                ErrorMessages::get('invalidEmail'),
                ErrorMessages::get('invalidTelephone'),
                ErrorMessages::get('invalidCustomTextfield')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidProcessId')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidAuthKey')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidFamilyName')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidEmail')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidTelephone')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidCustomTextfield')['statusCode'], $response->getStatusCode());
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
                ErrorMessages::get('invalidProcessId'),
                ErrorMessages::get('invalidAuthKey'),
                ErrorMessages::get('invalidFamilyName'),
                ErrorMessages::get('invalidEmail'),
                ErrorMessages::get('invalidTelephone')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidProcessId')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidAuthKey')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidFamilyName')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidEmail')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidTelephone')['statusCode'], $response->getStatusCode());
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
            'customTextfield' => ''
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidProcessId'),
                ErrorMessages::get('invalidAuthKey'),
                ErrorMessages::get('invalidFamilyName'),
                ErrorMessages::get('invalidEmail'),
                ErrorMessages::get('invalidCustomTextfield')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidProcessId')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidAuthKey')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidFamilyName')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidEmail')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidCustomTextfield')['statusCode'], $response->getStatusCode());
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
                ErrorMessages::get('invalidProcessId'),
                ErrorMessages::get('invalidAuthKey'),
                ErrorMessages::get('invalidFamilyName'),
                ErrorMessages::get('invalidEmail')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidProcessId')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidAuthKey')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidFamilyName')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidEmail')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidProcessid_InvalidAuthkey_InvalidFamilyname_ValidEmail_InvalidTelephone_InvalidCustomtextfield()
    {
        $parameters = [
            'processId' => null,
            'authKey' => '',
            'familyName' => '',
            'email' => 'test@muenchen.de',
            'telephone' => '123',
            'customTextfield' => ''
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidProcessId'),
                ErrorMessages::get('invalidAuthKey'),
                ErrorMessages::get('invalidFamilyName'),
                ErrorMessages::get('invalidTelephone'),
                ErrorMessages::get('invalidCustomTextfield')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidProcessId')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidAuthKey')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidFamilyName')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidTelephone')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidCustomTextfield')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidProcessid_InvalidAuthkey_InvalidFamilyname_ValidEmail_InvalidTelephone_ValidCustomtextfield()
    {
        $parameters = [
            'processId' => null,
            'authKey' => '',
            'familyName' => '',
            'email' => 'test@muenchen.de',
            'telephone' => '123',
            'customTextfield' => 'Some custom text'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidProcessId'),
                ErrorMessages::get('invalidAuthKey'),
                ErrorMessages::get('invalidFamilyName'),
                ErrorMessages::get('invalidTelephone')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidProcessId')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidAuthKey')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidFamilyName')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidTelephone')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidProcessid_InvalidAuthkey_InvalidFamilyname_ValidEmail_ValidTelephone_InvalidCustomtextfield()
    {
        $parameters = [
            'processId' => null,
            'authKey' => '',
            'familyName' => '',
            'email' => 'test@muenchen.de',
            'telephone' => '123456789',
            'customTextfield' => ''
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidProcessId'),
                ErrorMessages::get('invalidAuthKey'),
                ErrorMessages::get('invalidFamilyName'),
                ErrorMessages::get('invalidCustomTextfield')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidProcessId')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidAuthKey')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidFamilyName')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidCustomTextfield')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidProcessid_InvalidAuthkey_InvalidFamilyname_ValidEmail_ValidTelephone_ValidCustomtextfield()
    {
        $parameters = [
            'processId' => null,
            'authKey' => '',
            'familyName' => '',
            'email' => 'test@muenchen.de',
            'telephone' => '123456789',
            'customTextfield' => 'Some custom text'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidProcessId'),
                ErrorMessages::get('invalidAuthKey'),
                ErrorMessages::get('invalidFamilyName')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidProcessId')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidAuthKey')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidFamilyName')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidProcessid_InvalidAuthkey_ValidFamilyname_InvalidEmail_InvalidTelephone_InvalidCustomtextfield()
    {
        $parameters = [
            'processId' => null,
            'authKey' => '',
            'familyName' => 'TEST_USER',
            'email' => 'invalid-email',
            'telephone' => '123',
            'customTextfield' => ''
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidProcessId'),
                ErrorMessages::get('invalidAuthKey'),
                ErrorMessages::get('invalidEmail'),
                ErrorMessages::get('invalidTelephone'),
                ErrorMessages::get('invalidCustomTextfield')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidProcessId')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidAuthKey')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidEmail')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidTelephone')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidCustomTextfield')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidProcessid_InvalidAuthkey_ValidFamilyname_InvalidEmail_InvalidTelephone_ValidCustomtextfield()
    {
        $parameters = [
            'processId' => null,
            'authKey' => '',
            'familyName' => 'TEST_USER',
            'email' => 'invalid-email',
            'telephone' => '123',
            'customTextfield' => 'Some custom text'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidProcessId'),
                ErrorMessages::get('invalidAuthKey'),
                ErrorMessages::get('invalidEmail'),
                ErrorMessages::get('invalidTelephone')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidProcessId')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidAuthKey')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidEmail')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidTelephone')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidProcessid_InvalidAuthkey_ValidFamilyname_InvalidEmail_ValidTelephone_InvalidCustomtextfield()
    {
        $parameters = [
            'processId' => null,
            'authKey' => '',
            'familyName' => 'TEST_USER',
            'email' => 'invalid-email',
            'telephone' => '123456789',
            'customTextfield' => ''
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidProcessId'),
                ErrorMessages::get('invalidAuthKey'),
                ErrorMessages::get('invalidEmail'),
                ErrorMessages::get('invalidCustomTextfield')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidProcessId')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidAuthKey')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidEmail')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidCustomTextfield')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidProcessid_InvalidAuthkey_ValidFamilyname_InvalidEmail_ValidTelephone_ValidCustomtextfield()
    {
        $parameters = [
            'processId' => null,
            'authKey' => '',
            'familyName' => 'TEST_USER',
            'email' => 'invalid-email',
            'telephone' => '123456789',
            'customTextfield' => 'Some custom text'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidProcessId'),
                ErrorMessages::get('invalidAuthKey'),
                ErrorMessages::get('invalidEmail')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidProcessId')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidAuthKey')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidEmail')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidProcessid_InvalidAuthkey_ValidFamilyname_ValidEmail_InvalidTelephone_InvalidCustomtextfield()
    {
        $parameters = [
            'processId' => null,
            'authKey' => '',
            'familyName' => 'TEST_USER',
            'email' => 'test@muenchen.de',
            'telephone' => '123',
            'customTextfield' => ''
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidProcessId'),
                ErrorMessages::get('invalidAuthKey'),
                ErrorMessages::get('invalidTelephone'),
                ErrorMessages::get('invalidCustomTextfield')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidProcessId')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidAuthKey')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidTelephone')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidCustomTextfield')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidProcessid_InvalidAuthkey_ValidFamilyname_ValidEmail_InvalidTelephone_ValidCustomtextfield()
    {
        $parameters = [
            'processId' => null,
            'authKey' => '',
            'familyName' => 'TEST_USER',
            'email' => 'test@muenchen.de',
            'telephone' => '123',
            'customTextfield' => 'Some custom text'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidProcessId'),
                ErrorMessages::get('invalidAuthKey'),
                ErrorMessages::get('invalidTelephone')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidProcessId')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidAuthKey')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidTelephone')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidProcessid_InvalidAuthkey_ValidFamilyname_ValidEmail_ValidTelephone_InvalidCustomtextfield()
    {
        $parameters = [
            'processId' => null,
            'authKey' => '',
            'familyName' => 'TEST_USER',
            'email' => 'test@muenchen.de',
            'telephone' => '123456789',
            'customTextfield' => ''
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidProcessId'),
                ErrorMessages::get('invalidAuthKey'),
                ErrorMessages::get('invalidCustomTextfield')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidProcessId')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidAuthKey')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidCustomTextfield')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidProcessid_InvalidAuthkey_ValidFamilyname_ValidEmail_ValidTelephone_ValidCustomtextfield()
    {
        $parameters = [
            'processId' => null,
            'authKey' => '',
            'familyName' => 'TEST_USER',
            'email' => 'test@muenchen.de',
            'telephone' => '123456789',
            'customTextfield' => 'Some custom text'
        ];
        $response = $this->render([], $parameters, [], 'POST');
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

    public function testInvalidProcessid_ValidAuthkey_InvalidFamilyname_InvalidEmail_InvalidTelephone_InvalidCustomtextfield()
    {
        $parameters = [
            'processId' => null,
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
                ErrorMessages::get('invalidProcessId'),
                ErrorMessages::get('invalidFamilyName'),
                ErrorMessages::get('invalidEmail'),
                ErrorMessages::get('invalidTelephone'),
                ErrorMessages::get('invalidCustomTextfield')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidProcessId')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidFamilyName')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidEmail')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidTelephone')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidCustomTextfield')['statusCode'], $response->getStatusCode());
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
                ErrorMessages::get('invalidProcessId'),
                ErrorMessages::get('invalidFamilyName'),
                ErrorMessages::get('invalidEmail'),
                ErrorMessages::get('invalidTelephone')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidProcessId')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidFamilyName')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidEmail')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidTelephone')['statusCode'], $response->getStatusCode());
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
            'customTextfield' => ''
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidProcessId'),
                ErrorMessages::get('invalidFamilyName'),
                ErrorMessages::get('invalidEmail'),
                ErrorMessages::get('invalidCustomTextfield')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidProcessId')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidFamilyName')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidEmail')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidCustomTextfield')['statusCode'], $response->getStatusCode());
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
                ErrorMessages::get('invalidProcessId'),
                ErrorMessages::get('invalidFamilyName'),
                ErrorMessages::get('invalidEmail')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidProcessId')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidFamilyName')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidEmail')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidProcessid_ValidAuthkey_InvalidFamilyname_ValidEmail_InvalidTelephone_InvalidCustomtextfield()
    {
        $parameters = [
            'processId' => null,
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
                ErrorMessages::get('invalidProcessId'),
                ErrorMessages::get('invalidFamilyName'),
                ErrorMessages::get('invalidTelephone'),
                ErrorMessages::get('invalidCustomTextfield')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidProcessId')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidFamilyName')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidTelephone')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidCustomTextfield')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidProcessid_ValidAuthkey_InvalidFamilyname_ValidEmail_InvalidTelephone_ValidCustomtextfield()
    {
        $parameters = [
            'processId' => null,
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
                ErrorMessages::get('invalidProcessId'),
                ErrorMessages::get('invalidFamilyName'),
                ErrorMessages::get('invalidTelephone')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidProcessId')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidFamilyName')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidTelephone')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidProcessid_ValidAuthkey_InvalidFamilyname_ValidEmail_ValidTelephone_InvalidCustomtextfield()
    {
        $parameters = [
            'processId' => null,
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
                ErrorMessages::get('invalidProcessId'),
                ErrorMessages::get('invalidFamilyName'),
                ErrorMessages::get('invalidCustomTextfield')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidProcessId')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidFamilyName')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidCustomTextfield')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidProcessid_ValidAuthkey_InvalidFamilyname_ValidEmail_ValidTelephone_ValidCustomtextfield()
    {
        $parameters = [
            'processId' => null,
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
                ErrorMessages::get('invalidProcessId'),
                ErrorMessages::get('invalidFamilyName')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidProcessId')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidFamilyName')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidProcessid_ValidAuthkey_ValidFamilyname_InvalidEmail_InvalidTelephone_InvalidCustomtextfield()
    {
        $parameters = [
            'processId' => null,
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
                ErrorMessages::get('invalidProcessId'),
                ErrorMessages::get('invalidEmail'),
                ErrorMessages::get('invalidTelephone'),
                ErrorMessages::get('invalidCustomTextfield')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidProcessId')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidEmail')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidTelephone')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidCustomTextfield')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidProcessid_ValidAuthkey_ValidFamilyname_InvalidEmail_InvalidTelephone_ValidCustomtextfield()
    {
        $parameters = [
            'processId' => null,
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
                ErrorMessages::get('invalidProcessId'),
                ErrorMessages::get('invalidEmail'),
                ErrorMessages::get('invalidTelephone')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidProcessId')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidEmail')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidTelephone')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidProcessid_ValidAuthkey_ValidFamilyname_InvalidEmail_ValidTelephone_InvalidCustomtextfield()
    {
        $parameters = [
            'processId' => null,
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
                ErrorMessages::get('invalidProcessId'),
                ErrorMessages::get('invalidEmail'),
                ErrorMessages::get('invalidCustomTextfield')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidProcessId')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidEmail')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidCustomTextfield')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidProcessid_ValidAuthkey_ValidFamilyname_InvalidEmail_ValidTelephone_ValidCustomtextfield()
    {
        $parameters = [
            'processId' => null,
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
                ErrorMessages::get('invalidProcessId'),
                ErrorMessages::get('invalidEmail')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidProcessId')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidEmail')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidProcessid_ValidAuthkey_ValidFamilyname_ValidEmail_InvalidTelephone_InvalidCustomtextfield()
    {
        $parameters = [
            'processId' => null,
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
                ErrorMessages::get('invalidProcessId'),
                ErrorMessages::get('invalidTelephone'),
                ErrorMessages::get('invalidCustomTextfield')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidProcessId')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidTelephone')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidCustomTextfield')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidProcessid_ValidAuthkey_ValidFamilyname_ValidEmail_InvalidTelephone_ValidCustomtextfield()
    {
        $parameters = [
            'processId' => null,
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
                ErrorMessages::get('invalidProcessId'),
                ErrorMessages::get('invalidTelephone')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidProcessId')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidTelephone')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInvalidProcessid_ValidAuthkey_ValidFamilyname_ValidEmail_ValidTelephone_InvalidCustomtextfield()
    {
        $parameters = [
            'processId' => null,
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
                ErrorMessages::get('invalidProcessId'),
                ErrorMessages::get('invalidCustomTextfield')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidProcessId')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidCustomTextfield')['statusCode'], $response->getStatusCode());
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

    public function testValidProcessid_InvalidAuthkey_InvalidFamilyname_InvalidEmail_InvalidTelephone_InvalidCustomtextfield()
    {
        $parameters = [
            'processId' => '101002',
            'authKey' => '',
            'familyName' => '',
            'email' => 'invalid-email',
            'telephone' => '123',
            'customTextfield' => ''
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidAuthKey'),
                ErrorMessages::get('invalidFamilyName'),
                ErrorMessages::get('invalidEmail'),
                ErrorMessages::get('invalidTelephone'),
                ErrorMessages::get('invalidCustomTextfield')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidAuthKey')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidFamilyName')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidEmail')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidTelephone')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidCustomTextfield')['statusCode'], $response->getStatusCode());
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
                ErrorMessages::get('invalidAuthKey'),
                ErrorMessages::get('invalidFamilyName'),
                ErrorMessages::get('invalidEmail'),
                ErrorMessages::get('invalidTelephone')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidAuthKey')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidFamilyName')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidEmail')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidTelephone')['statusCode'], $response->getStatusCode());
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
            'customTextfield' => ''
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidAuthKey'),
                ErrorMessages::get('invalidFamilyName'),
                ErrorMessages::get('invalidEmail'),
                ErrorMessages::get('invalidCustomTextfield')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidAuthKey')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidFamilyName')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidEmail')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidCustomTextfield')['statusCode'], $response->getStatusCode());
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
                ErrorMessages::get('invalidAuthKey'),
                ErrorMessages::get('invalidFamilyName'),
                ErrorMessages::get('invalidEmail')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidAuthKey')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidFamilyName')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidEmail')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_InvalidAuthkey_InvalidFamilyname_ValidEmail_InvalidTelephone_InvalidCustomtextfield()
    {
        $parameters = [
            'processId' => '101002',
            'authKey' => '',
            'familyName' => '',
            'email' => 'test@muenchen.de',
            'telephone' => '123',
            'customTextfield' => ''
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidAuthKey'),
                ErrorMessages::get('invalidFamilyName'),
                ErrorMessages::get('invalidTelephone'),
                ErrorMessages::get('invalidCustomTextfield')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidAuthKey')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidFamilyName')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidTelephone')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidCustomTextfield')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_InvalidAuthkey_InvalidFamilyname_ValidEmail_InvalidTelephone_ValidCustomtextfield()
    {
        $parameters = [
            'processId' => '101002',
            'authKey' => '',
            'familyName' => '',
            'email' => 'test@muenchen.de',
            'telephone' => '123',
            'customTextfield' => 'Some custom text'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidAuthKey'),
                ErrorMessages::get('invalidFamilyName'),
                ErrorMessages::get('invalidTelephone')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidAuthKey')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidFamilyName')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidTelephone')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_InvalidAuthkey_InvalidFamilyname_ValidEmail_ValidTelephone_InvalidCustomtextfield()
    {
        $parameters = [
            'processId' => '101002',
            'authKey' => '',
            'familyName' => '',
            'email' => 'test@muenchen.de',
            'telephone' => '123456789',
            'customTextfield' => ''
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidAuthKey'),
                ErrorMessages::get('invalidFamilyName'),
                ErrorMessages::get('invalidCustomTextfield')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidAuthKey')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidFamilyName')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidCustomTextfield')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_InvalidAuthkey_InvalidFamilyname_ValidEmail_ValidTelephone_ValidCustomtextfield()
    {
        $parameters = [
            'processId' => '101002',
            'authKey' => '',
            'familyName' => '',
            'email' => 'test@muenchen.de',
            'telephone' => '123456789',
            'customTextfield' => 'Some custom text'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidAuthKey'),
                ErrorMessages::get('invalidFamilyName')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidAuthKey')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidFamilyName')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_InvalidAuthkey_ValidFamilyname_InvalidEmail_InvalidTelephone_InvalidCustomtextfield()
    {
        $parameters = [
            'processId' => '101002',
            'authKey' => '',
            'familyName' => 'TEST_USER',
            'email' => 'invalid-email',
            'telephone' => '123',
            'customTextfield' => ''
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidAuthKey'),
                ErrorMessages::get('invalidEmail'),
                ErrorMessages::get('invalidTelephone'),
                ErrorMessages::get('invalidCustomTextfield')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidAuthKey')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidEmail')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidTelephone')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidCustomTextfield')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_InvalidAuthkey_ValidFamilyname_InvalidEmail_InvalidTelephone_ValidCustomtextfield()
    {
        $parameters = [
            'processId' => '101002',
            'authKey' => '',
            'familyName' => 'TEST_USER',
            'email' => 'invalid-email',
            'telephone' => '123',
            'customTextfield' => 'Some custom text'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidAuthKey'),
                ErrorMessages::get('invalidEmail'),
                ErrorMessages::get('invalidTelephone')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidAuthKey')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidEmail')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidTelephone')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_InvalidAuthkey_ValidFamilyname_InvalidEmail_ValidTelephone_InvalidCustomtextfield()
    {
        $parameters = [
            'processId' => '101002',
            'authKey' => '',
            'familyName' => 'TEST_USER',
            'email' => 'invalid-email',
            'telephone' => '123456789',
            'customTextfield' => ''
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidAuthKey'),
                ErrorMessages::get('invalidEmail'),
                ErrorMessages::get('invalidCustomTextfield')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidAuthKey')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidEmail')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidCustomTextfield')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_InvalidAuthkey_ValidFamilyname_InvalidEmail_ValidTelephone_ValidCustomtextfield()
    {
        $parameters = [
            'processId' => '101002',
            'authKey' => '',
            'familyName' => 'TEST_USER',
            'email' => 'invalid-email',
            'telephone' => '123456789',
            'customTextfield' => 'Some custom text'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidAuthKey'),
                ErrorMessages::get('invalidEmail')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidAuthKey')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidEmail')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_InvalidAuthkey_ValidFamilyname_ValidEmail_InvalidTelephone_InvalidCustomtextfield()
    {
        $parameters = [
            'processId' => '101002',
            'authKey' => '',
            'familyName' => 'TEST_USER',
            'email' => 'test@muenchen.de',
            'telephone' => '123',
            'customTextfield' => ''
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidAuthKey'),
                ErrorMessages::get('invalidTelephone'),
                ErrorMessages::get('invalidCustomTextfield')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidAuthKey')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidTelephone')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidCustomTextfield')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_InvalidAuthkey_ValidFamilyname_ValidEmail_InvalidTelephone_ValidCustomtextfield()
    {
        $parameters = [
            'processId' => '101002',
            'authKey' => '',
            'familyName' => 'TEST_USER',
            'email' => 'test@muenchen.de',
            'telephone' => '123',
            'customTextfield' => 'Some custom text'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidAuthKey'),
                ErrorMessages::get('invalidTelephone')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidAuthKey')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidTelephone')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_InvalidAuthkey_ValidFamilyname_ValidEmail_ValidTelephone_InvalidCustomtextfield()
    {
        $parameters = [
            'processId' => '101002',
            'authKey' => '',
            'familyName' => 'TEST_USER',
            'email' => 'test@muenchen.de',
            'telephone' => '123456789',
            'customTextfield' => ''
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidAuthKey'),
                ErrorMessages::get('invalidCustomTextfield')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidAuthKey')['statusCode'], $response->getStatusCode());
        $this->assertEquals(ErrorMessages::get('invalidCustomTextfield')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testValidProcessid_InvalidAuthkey_ValidFamilyname_ValidEmail_ValidTelephone_ValidCustomtextfield()
    {
        $parameters = [
            'processId' => '101002',
            'authKey' => '',
            'familyName' => 'TEST_USER',
            'email' => 'test@muenchen.de',
            'telephone' => '123456789',
            'customTextfield' => 'Some custom text'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidAuthKey')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidAuthKey')['statusCode'], $response->getStatusCode());
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
