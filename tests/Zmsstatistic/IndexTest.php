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
}
