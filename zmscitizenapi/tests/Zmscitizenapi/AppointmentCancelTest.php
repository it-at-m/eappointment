<?php

namespace BO\Zmscitizenapi\Tests;

use BO\Zmscitizenapi\Localization\ErrorMessages;

class AppointmentCancelTest extends Base
{
    protected $classname = "\BO\Zmscitizenapi\Controllers\AppointmentCancel";

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
        $processResponse = $this->readFixture("GET_process.json");
        $processData = json_decode($processResponse, true);
        $processData['data']['appointments'][0]['date'] = time() + 86400; 
    
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/process/101002/fb43/',
                    'parameters' => [
                        'resolveReferences' => 2,
                    ],
                    'response' => json_encode($processData)
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
                    'url' => '/process/101002/fb43/delete/mail/',
                    'response' => $this->readFixture("POST_cancel_appointment.json")
                ],
                [
                    'function' => 'readDeleteResult',
                    'url' => '/process/101002/fb43/',
                    'parameters' => [],
                    'response' => $this->readFixture("POST_cancel_appointment.json")
                ]
            ]
        );
    
        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        
        $expectedResponse = [
            'processId' => 101002,
            'timestamp' => '1727865900',
            'authKey' => 'fb43',
            'familyName' => 'TEST_USER',
            'customTextfield' => 'Some custom text',
            'email' => 'test@muenchen.de',
            'telephone' => '123456789',
            'officeName' => null,
            'officeId' => 0,
            'scope' => [
                'id' => 0,
                'provider' => null,
                'shortName' => null,
                'telephoneActivated' => null,
                'telephoneRequired' => null,
                'customTextfieldActivated' => null,
                'customTextfieldRequired' => null,
                'customTextfieldLabel' => null,
                'captchaActivatedRequired' => null,
                'displayInfo' => null
            ],
            'subRequestCounts' => [],
            'serviceId' => 10242339,
            'serviceCount' => 1,
            'status' => 'deleted'
        ];
    
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($expectedResponse, $responseBody);
    }

    public function testPastAppointment()
    {
        $processResponse = $this->readFixture("GET_process.json");
        $processData = json_decode($processResponse, true);
        $processData['data']['appointments'][0]['date'] = time() - 3600; // 1 hour ago
        
        $this->setApiCalls([
            [
                'function' => 'readGetResult',
                'url' => '/process/101002/fb43/',
                'parameters' => [
                    'resolveReferences' => 2,
                ],
                'response' => json_encode($processData)
            ],
            [
                'function' => 'readGetResult',
                'url' => '/source/unittest/',
                'parameters' => [
                    'resolveReferences' => 2,
                ],
                'response' => $this->readFixture("GET_SourceGet_dldb.json")
            ]
        ]);
    
        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
    
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEqualsCanonicalizing(
            ['errors' => [ErrorMessages::get('appointmentCanNotBeCanceled')]],
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

        $this->assertEquals(404, $response->getStatusCode());
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

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEqualsCanonicalizing(
            ['errors' => [ErrorMessages::get('authKeyMismatch')]],
            $responseBody
        );
    }

    public function testInvalidProcessId()
    {
        $parameters = [
            'processId' => null,
            'authKey' => 'fb43'
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $this->assertEquals(400, $response->getStatusCode());
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

        $this->assertEquals(400, $response->getStatusCode());
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

        $this->assertEquals(400, $response->getStatusCode());
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

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEqualsCanonicalizing(
            ['errors' => [ErrorMessages::get('invalidAuthKey')]],
            $responseBody
        );
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
}