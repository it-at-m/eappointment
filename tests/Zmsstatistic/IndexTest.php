<?php

namespace BO\Zmsstatistic\Tests;

class IndexTest extends Base
{
    protected $classname = "Index";

    protected $arguments = [ ];

    protected $parameters = [
        'loginName' => 'testadmin',
        'password' => 'vorschau',
        'login_form_validate' => 1
    ];

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
        $response = $this->render([ ], [ ], [ ]);
        $this->assertContains('Statistik - Anmeldung für Behördenmitarbeiter', (string) $response->getBody());
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

    public function testAlreadyLoggedIn()
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
        $this->assertContains('Willkommen zurück', (string)$response->getBody());
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
        $this->assertContains(
            'Das eingegebene Passwort und der Nutzername passen nicht zusammen',
            (string)$response->getBody()
        );
        $this->assertContains('form-group has-error', (string)$response->getBody());
    }

    public function testDoubleLogin()
    {
        $this->expectException('\BO\Zmsclient\Exception');
        $exception = new \BO\Zmsclient\Exception();
        $exception->template = 'BO\Zmsapi\Exception\Useraccount\UserAlreadyLoggedIn';
        $exception->data = json_decode($this->readFixture("GET_Workstation_Resolved2.json"), 1)['data'];
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/workstation/login/',
                    'exception' => $exception
                ]
            ]
        );
        $this->render($this->arguments, $this->parameters, [], 'POST');
        $this->assertEquals('8520c285985b5bd209a0110442dc4e45', \BO\Zmsclient\Auth::getKey());
    }
}
