<?php

namespace BO\Zmsadmin\Tests;

class UseraccountEditTest extends Base
{
    protected $arguments = [
        'loginname' => 'testuser'
    ];

    protected $parameters = [];

    protected $classname = "UseraccountEdit";

    public function testRendering()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/useraccount/testuser/',
                    'response' => $this->readFixture("GET_useraccount_testuser.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/owner/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_owner.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/config/',
                    'parameters' => [],
                    'xtoken' => 'a9b215f1-e460-490c-8a0b-6d42c274d5e4',
                    'response' => $this->readFixture("GET_config.json"),
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertStringContainsString('value="testuser"', (string)$response->getBody());
        $this->assertStringContainsString('Nutzer: Einrichtung und Administration', (string)$response->getBody());
        $this->assertStringNotContainsString(
            'Dieser Nutzer wurde über einen OpenID Connect Anbieter angelegt.',
            (string)$response->getBody()
        );
        $this->assertStringContainsString(
            'Passwortwiederholung',
            (string)$response->getBody()
        );
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRenderingSave()
    {
        \App::$now = new \DateTimeImmutable('2016-04-01 11:55:00', new \DateTimeZone('Europe/Berlin'));
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/useraccount/testuser/',
                    'response' => $this->readFixture("GET_useraccount_testuser.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/owner/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_owner.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/useraccount/testuser/',
                    'response' => $this->readFixture("GET_useraccount_testuser.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, [
            'id' => 'unittest',
            'changePassword' => array(
                'passwort',
                'passwort',
            ),
            'departments' => array(
                ['id' => 74],
                ['id' => 57],
            ),
            'rights' => array(
                'sms' => '1',
                'ticketprinter' => '1',
                'availability' => '1',
                'scope' => '1'
            ),
            'save' => 'save'
        ], [], 'POST');
        $this->assertRedirect($response, '/useraccount/testuser/?success=useraccount_saved');
        $this->assertEquals(302, $response->getStatusCode());
    }

    // passwords not equal
    public function testRenderingSaveFailedValidation()
    {
        $exception = new \BO\Zmsclient\Exception();
        $exception->template = 'BO\Zmsentities\Exception\Schemavalidation';
        $exception->data['changePassword']['messages'] = ['Die Länge des Passworts muss mindestens 6 Zeichen betragen'];

        \App::$now = new \DateTimeImmutable('2016-04-01 11:55:00', new \DateTimeZone('Europe/Berlin'));
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/useraccount/testuser/',
                    'response' => $this->readFixture("GET_useraccount_testuser.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/owner/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_owner.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/useraccount/testuser/',
                    'exception' => $exception
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/config/',
                    'parameters' => [],
                    'xtoken' => 'a9b215f1-e460-490c-8a0b-6d42c274d5e4',
                    'response' => $this->readFixture("GET_config.json"),
                ]
            ]
        );
        $response = $this->render($this->arguments, [
            'id' => 'unittest',
            'changePassword' => array(
                'passwort',
                'passwort2',
            ),
            'departments' => array(
                ['id' => 74],
                ['id' => 57],
            ),
            'rights' => array(
                'sms' => '1',
                'ticketprinter' => '1',
                'availability' => '1',
                'scope' => '1'
            ),
            'save' => 'save'
        ], [], 'POST');

        $this->assertStringContainsString('board exception', (string)$response->getBody());
        $this->assertStringContainsString('Passworts muss mindestens 6 Zeichen betragen', (string)$response->getBody());
    }

    // no department selected
    public function testRenderingSaveFailedNoDepartment()
    {
        $exception = new \BO\Zmsclient\Exception();
        $exception->template = 'BO\Zmsentities\Exception\Schemavalidation';
        $exception->data['departments[][id]']['messages'] = [
          'Es muss mindestens eine Behörde oder systemübergreifend ausgewählt werden'
        ];

        \App::$now = new \DateTimeImmutable('2016-04-01 11:55:00', new \DateTimeZone('Europe/Berlin'));
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/useraccount/testuser/',
                    'response' => $this->readFixture("GET_useraccount_testuser.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/owner/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_owner.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/useraccount/testuser/',
                    'exception' => $exception
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/config/',
                    'parameters' => [],
                    'xtoken' => 'a9b215f1-e460-490c-8a0b-6d42c274d5e4',
                    'response' => $this->readFixture("GET_config.json"),
                ]
            ]
        );
        $response = $this->render($this->arguments, [
            'id' => 'unittest',
            'changePassword' => array(
                '',
                '',
            ),
            'rights' => array(
                'sms' => '1',
                'ticketprinter' => '1',
                'availability' => '1',
                'scope' => '1'
            ),
            'save' => 'save'
        ], [], 'POST');

        $this->assertStringContainsString('board exception', (string)$response->getBody());
        $this->assertStringContainsString(
            'Es muss mindestens eine Behörde oder systemübergreifend ausgewählt werden',
            (string)$response->getBody()
        );
    }

    public function testUnkownException()
    {
        $this->expectException('BO\Zmsclient\Exception');
        $exception = new \BO\Zmsclient\Exception();
        $exception->template = '';

        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/useraccount/testuser/',
                    'response' => $this->readFixture("GET_useraccount_testuser.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/owner/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_owner.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/useraccount/testuser/',
                    'exception' => $exception
                ]
            ]
        );
        $this->render($this->arguments, [
            'id' => 'unittest',
            'changePassword' => array(
                '',
                '',
            ),
            'rights' => array(
                'sms' => '1',
                'ticketprinter' => '1',
                'availability' => '1',
                'scope' => '1'
            ),
            'save' => 'save'
        ], [], 'POST');
    }
}
