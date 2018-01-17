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
                ]
            ]
        );
        $response = $this->render($this->arguments, [], []);
        $this->assertContains('Anmeldung', (string)$response->getBody());
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

    public function testLoginFailed()
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
                    'response' => $this->readFixture("GET_Workstation_UserAccountMissingLogin.json")
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

    public function testLoginFailedAuthKeyFound()
    {
        $this->expectException('\BO\Zmsclient\Exception');
        $exception = new \BO\Zmsclient\Exception();
        $exception->template = 'BO\Zmsapi\Exception\Useraccount\AuthKeyFound';

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
        $this->render($this->arguments, $this->parameters, [], 'POST');
    }

    public function testLoginValidationError()
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
        $response = $this->render($this->arguments, [
            'loginName' => 'testadmin',
            'login_form_validate' => 1
        ], [], 'POST');
        $this->assertContains('Es muss ein Passwort eingegeben werden', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }
}
