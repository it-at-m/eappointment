<?php

namespace BO\Zmsadmin\Tests;

class UseraccountAddTest extends Base
{
    protected $arguments = [];

    protected $parameters = [
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
    ];

    protected $classname = "UseraccountAdd";

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
                    'url' => '/owner/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_ownerlist.json")
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
        $this->assertStringContainsString('Nutzer: Einrichtung und Administration', (string)$response->getBody());
        $this->assertStringContainsString('Nutzer anlegen', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testSave()
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
                    'url' => '/owner/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_ownerlist.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/useraccount/',
                    'response' => $this->readFixture("GET_useraccount_unittest.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, [], 'POST');
        $this->assertRedirect($response, '/useraccount/unittest/?success=useraccount_added');
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testValidation()
    {
        $exception = new \BO\Zmsclient\Exception();
        $exception->template = 'BO\Zmsentities\Exception\SchemaValidation';
        $exception->data['password']['messages'] = [
            'Das Passwort muss mindestens 6 Zeichen lang sein.'
        ];

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
                    'url' => '/owner/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_ownerlist.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/useraccount/',
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
        $response = $this->render($this->arguments, $this->parameters, [], 'POST');
        $this->assertStringContainsString(
            'Das Passwort muss mindestens 6 Zeichen lang sein.',
            (string)$response->getBody()
        );
        $this->assertEquals(200, $response->getStatusCode());
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
                    'url' => '/owner/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_ownerlist.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/useraccount/',
                    'exception' => $exception
                ]
            ]
        );
        $this->render($this->arguments, $this->parameters, [], 'POST');
    }
}
