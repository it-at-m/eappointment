<?php

namespace BO\Zmsadmin\Tests;

class QuickLoginTest extends Base
{
    protected $arguments = [];

    protected $parameters = [
        'scope' => 141,
        'workstation' => 12,
        'hint' => 'Hinten%20anstellen',
        'loginName' => 'testadmin',
        'password' => 'vorschau',
        'url' => '/workstation/'
    ];

    protected $classname = "QuickLogin";

    public function testRendering()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readPostResult',
                    'url' => '/workstation/login/',
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/workstation/',
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertRedirect($response, '/workstation');
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testLoginWithEnablingClusterWithAppointmentsOnly()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readPostResult',
                    'url' => '/workstation/login/',
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/workstation/',
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ]
            ]
        );
        $parameters = $this->parameters;
        $parameters['scope'] = 'cluster';
        $parameters['appointmentsOnly'] = 1;
        $response = $this->render($this->arguments, $parameters, []);
        $this->assertRedirect($response, '/workstation');
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testRenderingLoginFailed()
    {
        $this->expectException('\BO\Zmsentities\Exception\QuickLoginFailed');
        $parameters = $this->parameters;
        unset($parameters['loginName']);
        $this->render($this->arguments, $parameters, []);
    }

    public function testRenderingDoubleLogin()
    {
        $exception = new \BO\Zmsclient\Exception();
        $exception->template = 'BO\Zmsapi\Exception\Useraccount\UserAlreadyLoggedIn';
        $exception->data = json_decode($this->readFixture("GET_Workstation_Resolved2.json"), 1)['data'];

        $this->setApiCalls(
            [
                [
                    'function' => 'readPostResult',
                    'url' => '/workstation/login/',
                    'exception' => $exception
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/workstation/',
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertRedirect($response, '/workstation');
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('8520c285985b5bd209a0110442dc4e45', \BO\Zmsclient\Auth::getKey());
    }
}
