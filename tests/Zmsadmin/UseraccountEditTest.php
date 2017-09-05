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
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertContains('value="testuser"', (string)$response->getBody());
        $this->assertContains('Nutzer: Einrichtung und Administration', (string)$response->getBody());
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
                    'url' => '/workstation/password/',
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
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
        $this->assertRedirect($response, '/useraccount/testuser/?confirm_success=1459504500');
        $this->assertEquals(302, $response->getStatusCode());
    }

    // passwords not equal
    public function testRenderingSaveFailedValidation()
    {
        $this->expectException('\BO\Zmsclient\Exception');
        $exception = new \BO\Zmsclient\Exception();
        $exception->template = 'BO\Zmsentities\Exception\Schemavalidation';
        $exception->data = json_decode($this->readFixture("GET_useraccount_testuser.json"), 1)['data'];

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
                    'url' => '/workstation/password/',
                    'exception' => $exception
                ]
            ]
        );
        $this->render($this->arguments, [
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
    }

    // no department selected
    public function testRenderingSaveFailedNoDepartment()
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
                ]
            ]
        );
        $response = $this->render($this->arguments, [
            'id' => 'unittest',
            'changePassword' => array(
                'passwort',
                'passwort',
            ),
            'rights' => array(
                'sms' => '1',
                'ticketprinter' => '1',
                'availability' => '1',
                'scope' => '1'
            ),
            'save' => 'save'
        ], [], 'POST');
        $this->assertContains(
            'Es muss mindestens eine Behörde oder systemübergreifend ausgewählt werden',
            (string)$response->getBody()
        );
        $this->assertEquals(200, $response->getStatusCode());
    }

    // superuser rights needed for all departments
    public function testRenderingSaveFailedNotSuperuser()
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
                ['id' => 0]
            ),
            'rights' => array(
                'sms' => '1',
                'ticketprinter' => '1',
                'availability' => '1',
                'scope' => '1'
            ),
            'save' => 'save'
        ], [], 'POST');
        $this->assertContains(
            'Für &quot;systemübergreifend&quot; muss die Superuser-Berechtigung ausgewählt werden',
            (string)$response->getBody()
        );
        $this->assertEquals(200, $response->getStatusCode());
    }
}
