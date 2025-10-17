<?php

namespace BO\Zmscitizenapi\Tests\Controllers\Appointment;

use BO\Zmscitizenapi\Utils\ErrorMessages;
use BO\Zmscitizenapi\Tests\ControllerTestCase;

class AppointmentPreconfirmControllerTest extends ControllerTestCase
{
    protected $classname = "\BO\Zmscitizenapi\Controllers\Appointment\AppointmentPreconfirmController";

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
                    'xtoken' => 'hash',
                    'response' => $this->readFixture("GET_merged_mailtemplates.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/preconfirmed/',
                    'response' => $this->readFixture("POST_preconfirm_appointment.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/101002/fb43/preconfirmation/mail/',
                    'response' => $this->readFixture("POST_preconfirm_appointment.json")
                ]
            ]
        );

        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $this->assertArrayHasKey('captchaToken', $responseBody);
        $this->assertIsString($responseBody['captchaToken']);
        unset($responseBody['captchaToken']);

        $expectedResponse = [
            'processId' => 101002,
            'timestamp' => '1727865900',
            'authKey' => 'fb43',
            'familyName' => 'TEST_USER',
            'customTextfield' => 'Some custom text',
            'customTextfield2' => 'Another custom text',
            'email' => 'test@muenchen.de',
            'telephone' => '123456789',
            'officeName' => null,
            'officeId' => 0,
            'scope' => [
                'id' => 0,
                'provider' => [
                    'contact'=> null,
                    'id'=> 0,
                    'lat'=> null,
                    'lon'=> null,
                    'name'=> '',
                    'displayName'=> '',
                    'source'=> 'dldb'
                ],
                'shortName' => '',
                'emailFrom' => '',
                'emailRequired' => null,
                'telephoneActivated' => null,
                'telephoneRequired' => null,
                'customTextfieldActivated' => null,
                'customTextfieldRequired' => null,
                'customTextfieldLabel' => null,
                'customTextfield2Activated' => null,
                'customTextfield2Required' => null,
                'customTextfield2Label' => null,
                'captchaActivatedRequired' => null,
                'infoForAppointment' => null,
                'infoForAllAppointments' => null,
                'slotsPerAppointment' => null,
                "appointmentsPerMail" => null,
                "whitelistedMails" => null,
                "reservationDuration" => null,
                "activationDuration" => null,
                "hint" => null
            ],
            'subRequestCounts' => [],
            'serviceId' => 10242339,
            'serviceName' => 'Adressänderung Personalausweis, Reisepass, eAT',
            'serviceCount' => 1,
            'status' => 'preconfirmed',
            'slotCount' => 1,
        ];

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertArrayHasKey('icsContent', $responseBody);
        $this->assertStringContainsString('BEGIN:VCALENDAR', $responseBody['icsContent']);
        unset($responseBody['icsContent']);
        unset($expectedResponse['icsContent']);
        $this->assertEquals($expectedResponse, $responseBody);
    }

    public function testInvalidProcessId()
    {
        $parameters = [
            'processId' => null,
            'authKey' => 'fb43'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $this->assertEquals(ErrorMessages::get('invalidProcessId')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing(
            ['errors' => [ErrorMessages::get('invalidProcessId')]],
            $responseBody
        );
    }

    public function testInvalidAuthKey()
    {
        $parameters = [
            'processId' => '101002',
            'authKey' => ''
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $this->assertEquals(ErrorMessages::get('invalidAuthKey')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing(
            ['errors' => [ErrorMessages::get('invalidAuthKey')]],
            $responseBody
        );
    }

    public function testMissingProcessId()
    {
        $parameters = [
            'authKey' => 'fb43'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $this->assertEquals(ErrorMessages::get('invalidProcessId')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing(
            ['errors' => [ErrorMessages::get('invalidProcessId')]],
            $responseBody
        );
    }

    public function testMissingAuthKey()
    {
        $parameters = [
            'processId' => '101002'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $this->assertEquals(ErrorMessages::get('invalidAuthKey')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing(
            ['errors' => [ErrorMessages::get('invalidAuthKey')]],
            $responseBody
        );
    }

    public function testNoEmailSendingWhenStatusNotPreconfirmed()
    {
        $processResponse = $this->readFixture("POST_preconfirm_appointment.json");
        $processData = json_decode($processResponse, true);
        $processData['data']['queue']['status'] = 'confirmed'; // Change status to something else
        
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
                'xtoken' => 'hash',
                'response' => $this->readFixture("GET_merged_mailtemplates.json")
            ],
            [
                'function' => 'readPostResult',
                'url' => '/process/status/preconfirmed/',
                'response' => json_encode($processData)
            ]
        ]);

        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('confirmed', $responseBody['status']);
    }

    public function testInvalidRequest()
    {
        $response = $this->render([], [], [], 'GET'); // Using GET instead of POST
        $responseBody = json_decode((string) $response->getBody(), true);

        $this->assertEquals(ErrorMessages::get('invalidRequest')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing(
            ['errors' => [ErrorMessages::get('invalidRequest')]],
            $responseBody
        );
    }

    public function testPreconfirmationExpired()
    {
        $exception = new \BO\Zmsclient\Exception();
        $exception->template = 'BO\\Zmsapi\\Exception\\Process\\PreconfirmationExpired';
    
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
                'xtoken' => 'hash',
                'response' => $this->readFixture("GET_merged_mailtemplates.json")
            ],
            [
                'function' => 'readPostResult',
                'url' => '/process/status/preconfirmed/',
                'exception' => $exception
            ]
        ]);
    
        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true); 
        $this->assertEquals(ErrorMessages::get('preconfirmationExpired')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing(
            ['errors' => [ErrorMessages::get('preconfirmationExpired')]],
            $responseBody
        );
    }

    public function testAppointmentNotFoundException()
    {
        $exception = new \BO\Zmsclient\Exception();
        $exception->template = 'BO\\Zmsapi\\Exception\\Process\\ProcessNotFound';

        $this->setApiCalls([
            [
                'function' => 'readGetResult',
                'url' => '/process/999999/fb43/',
                'parameters' => [
                    'resolveReferences' => 2,
                ],
                'exception' => $exception
            ]
        ]);

        $parameters = [
            'processId' => '999999',
            'authKey' => 'fb43'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $this->assertEquals(ErrorMessages::get('appointmentNotFound')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing(
            ['errors' => [ErrorMessages::get('appointmentNotFound')]],
            $responseBody
        );
    }

    public function testAuthKeyMismatch()
    {
        $exception = new \BO\Zmsclient\Exception();
        $exception->template = 'BO\\Zmsapi\\Exception\\Process\\AuthKeyMatchFailed';

        $this->setApiCalls([
            [
                'function' => 'readGetResult',
                'url' => '/process/101002/wrongkey/',
                'parameters' => [
                    'resolveReferences' => 2,
                ],
                'exception' => $exception
            ]
        ]);

        $parameters = [
            'processId' => '101002',
            'authKey' => 'wrongkey'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $this->assertEquals(ErrorMessages::get('authKeyMismatch')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing(
            ['errors' => [ErrorMessages::get('authKeyMismatch')]],
            $responseBody
        );
    }

    public function testTooManyEmailsAtLocation()
    {
        $exception = new \BO\Zmsclient\Exception();
        $exception->template = 'BO\\Zmsapi\\Exception\\Process\\MoreThanAllowedAppointmentsPerMail';

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
                'xtoken' => 'hash',
                'response' => $this->readFixture("GET_merged_mailtemplates.json")
            ],
            [
                'function' => 'readPostResult',
                'url' => '/process/status/preconfirmed/',
                'exception' => $exception
            ]
        ]);

        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $this->assertEquals(ErrorMessages::get('tooManyAppointmentsWithSameMail')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing(
            ['errors' => [ErrorMessages::get('tooManyAppointmentsWithSameMail')]],
            $responseBody
        );
    }

    public function testEmailRequired()
    {
        $exception = new \BO\Zmsclient\Exception();
        $exception->template = 'BO\\Zmsapi\\Exception\\Process\\EmailRequired';
    
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
                'xtoken' => 'hash',
                'response' => $this->readFixture("GET_merged_mailtemplates.json")
            ],
            [
                'function' => 'readPostResult',
                'url' => '/process/status/preconfirmed/',
                'exception' => $exception
            ]
        ]);

        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
    
        $this->assertEquals(ErrorMessages::get('emailIsRequired')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing(
            ['errors' => [ErrorMessages::get('emailIsRequired')]],
            $responseBody
        );
    }

    public function testTelephoneRequired()
    {
        $exception = new \BO\Zmsclient\Exception();
        $exception->template = 'BO\\Zmsapi\\Exception\\Process\\TelephoneRequired';
    
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
                'xtoken' => 'hash',
                'response' => $this->readFixture("GET_merged_mailtemplates.json")
            ],
            [
                'function' => 'readPostResult',
                'url' => '/process/status/preconfirmed/',
                'exception' => $exception
            ]
        ]);

        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
    
        $this->assertEquals(ErrorMessages::get('telephoneIsRequired')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing(
            ['errors' => [ErrorMessages::get('telephoneIsRequired')]],
            $responseBody
        );
    }

    public function testProcessNotReservedAnymore()
    {
        $exception = new \BO\Zmsclient\Exception();
        $exception->template = 'BO\\Zmsapi\\Exception\\Process\\ProcessNotReservedAnymore';
    
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
                'xtoken' => 'hash',
                'response' => $this->readFixture("GET_merged_mailtemplates.json")
            ],
            [
                'function' => 'readPostResult',
                'url' => '/process/status/preconfirmed/',
                'exception' => $exception
            ]
        ]);

        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
    
        $this->assertEquals(ErrorMessages::get('processNotReservedAnymore')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing(
            ['errors' => [ErrorMessages::get('processNotReservedAnymore')]],
            $responseBody
        );
    }

}