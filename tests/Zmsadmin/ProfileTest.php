<?php

namespace BO\Zmsadmin\Tests;

class ProfileTest extends Base
{
    protected $arguments = [];

    protected $parameters = [];

    protected $classname = "Profile";

    /*
    public function testRendering()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertStringContainsString('Nutzerinformation', (string)$response->getBody());
        $this->assertStringContainsString('testadmin', (string)$response->getBody());
        $this->assertStringContainsString('value="0"', (string)$response->getBody());
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

    public function testUpdateFailed()
    {
        $exception = new \BO\Zmsclient\Exception();
        $exception->template = 'BO\Zmsentities\Exception\SchemaValidation';
        $exception->data['password']['messages'] = [
            'Nutzername oder das Passwort wurden falsch eingegeben'
        ];

        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/workstation/password/',
                    'exception' => $exception
                ]
            ]
        );
        $response = $this->render($this->arguments, [
            'id' => 'testadmin',
            'password' => 'vorschau',
            'changePassword' => ['myPassword', 'myPassword'],
            'save' => 'save'
        ], [], 'POST');
        $this->assertStringContainsString(
            'Nutzername oder das Passwort wurden falsch eingegeben',
            (string)$response->getBody()
        );
    }

    public function testUnknownException()
    {
        $this->expectException('BO\Zmsclient\Exception');
        $exception = new \BO\Zmsclient\Exception();
        $exception->template = '';

        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/workstation/password/',
                    'exception' => $exception
                ]
            ]
        );
        $this->render($this->arguments, [
            'id' => 'testadmin',
            'password' => 'vorschau',
            'changePassword' => ['myPassword', 'myPassword'],
            'save' => 'save'
        ], [], 'POST');
    }

    public function testRenderingUpdate()
    {
        \App::$now = new \DateTimeImmutable('2016-04-01 11:55:00', new \DateTimeZone('Europe/Berlin'));
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/workstation/password/',
                    'response' => $this->readFixture("GET_useraccount_testuser.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, [
            'id' => 'testadmin',
            'password' => 'vorschau',
            'changePassword' => ['myPassword', 'myPassword'],
            'save' => 'save'
        ], [], 'POST');
        $this->assertRedirect($response, '/profile/?success=useraccount_saved');
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testRenderingUpdateValidationFailed()
    {
        $this->expectException('\BO\Zmsentities\Exception\SchemaValidation');
        $exception = new \BO\Zmsentities\Exception\SchemaValidation();

        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/workstation/password/',
                    'exception' => $exception
                ]
            ]
        );
        $this->render($this->arguments, [
            'id' => 'testadmin',
            'password' => 'vorschau',
            'changePassword' => ['myPassword', 'myPassword2'],
            'save' => 'save'
        ], [], 'POST');
    }*/
}
