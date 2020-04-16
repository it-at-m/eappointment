<?php

namespace BO\Zmsadmin\Tests;

class ChangelogTest extends Base
{
    protected $arguments = [];

    protected $parameters = [];

    protected $classname = "Changelog";

    public function testRendering()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture("GET_Workstation_UserAccountMissingLogin.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertContains('Changelog', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testLoggedIn()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertContains('Changelog', (string)$response->getBody());
        $this->assertContains('Bürgeramt Heerstraße', (string)$response->getBody());
        $this->assertContains('Sachbearbeiterplatz', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }
}
