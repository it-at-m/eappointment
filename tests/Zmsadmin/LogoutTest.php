<?php

namespace BO\Zmsadmin\Tests;

class LogoutTest extends Base
{
    protected $arguments = [];

    protected $parameters = [];

    protected $classname = "Logout";

    public function testRendering()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 0],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readDeleteResult',
                    'url' => '/workstation/login/testadmin/',
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, [], 'POST');
        $this->assertStringContainsString('Erfolgreich abgemeldet', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testLogoutFailed()
    {
        $this->expectException('\BO\Zmsclient\Exception');
        $exception = new \BO\Zmsclient\Exception();

        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 0],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readDeleteResult',
                    'url' => '/workstation/login/testadmin/',
                    'exception' => $exception
                ]
            ]
        );

        $this->render($this->arguments, $this->parameters, [], 'POST');
    }
}
