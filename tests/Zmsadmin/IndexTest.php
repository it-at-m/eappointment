<?php

namespace BO\Zmsadmin\Tests;

class IndexTest extends Base
{
    protected $arguments = [];

    protected $parameters = [
        'loginName' => 'testadmin',
        'password' => 'vorschau',
        'login_form_validate' => 1
    ];

    protected $classname = "Index";

    public function testRendering()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'response' => $this->readFixture("GET_Workstation_UserAccountMissingLogin.json")
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
        $response = $this->render($this->arguments, [], []);
        $this->assertStringContainsString('Anmeldung', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testLogin()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'response' => $this->readFixture("GET_Workstation_UserAccountMissingLogin.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/workstation/login/',
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, [], 'POST');
        $this->assertRedirect($response, '/workstation/select/');
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testWelcomeBack()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, [], []);
        $this->assertStringContainsString('Willkommen zurück', (string)$response->getBody());
    }

    public function testUnknownException()
    {
        $this->expectException('\BO\Zmsclient\Exception');
        $exception = new \BO\Zmsclient\Exception();
        $exception->template = '';
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'exception' => $exception
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/workstation/login/',
                    'exception' => $exception
                ]
            ]
        );
        $this->render($this->arguments, $this->parameters, [], 'POST');
    }

    public function testAlreadyLoggedIn()
    {
        $this->expectException('\BO\Zmsclient\Exception');
        $exception = new \BO\Zmsclient\Exception();
        $exception->template = 'BO\Zmsapi\Exception\Useraccount\UserAlreadyLoggedIn';
        $exception->data['authkey'] = 'unit';
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'exception' => $exception
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/workstation/login/',
                    'exception' => $exception
                ]
            ]
        );
        $this->render($this->arguments, $this->parameters, [], 'POST');
    }

    public function testLoginFailedBySchemaValidation()
    {
        $exception = new \BO\Zmsclient\Exception();
        $exception->template = 'BO\Zmsentities\Exception\SchemaValidation';
        $exception->data['password']['messages'] = [
            'Der Nutzername oder das Passwort wurden falsch eingegeben'
        ];

        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'response' => $this->readFixture("GET_Workstation_UserAccountMissingLogin.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/workstation/login/',
                    'exception' => $exception
                ]
            ]
        );
        $response = $this->render($this->arguments, [
            'loginName' => 'un',
            'password' => 'test',
            'login_form_validate' => 1
        ], [], 'POST');
        $this->assertStringContainsString(
            'Das eingegebene Passwort und der Nutzername passen nicht zusammen',
            (string)$response->getBody()
        );
        $this->assertStringContainsString('form-group has-error', (string)$response->getBody());
    }

    public function testLoginFailed()
    {
        $exception = new \BO\Zmsclient\Exception();
        $exception->template = 'BO\Zmsapi\Exception\Useraccount\InvalidCredentials';
        $exception->data['password']['messages'] = [
            'Der Nutzername oder das Passwort wurden falsch eingegeben'
        ];

        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'response' => $this->readFixture("GET_Workstation_UserAccountMissingLogin.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/workstation/login/',
                    'exception' => $exception
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, [], 'POST');
        $this->assertStringContainsString(
            'Das eingegebene Passwort und der Nutzername passen nicht zusammen',
            (string)$response->getBody()
        );
        $this->assertStringContainsString('form-group has-error', (string)$response->getBody());
    }
}
