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
        $this->assertStringContainsString('Changelog', (string)$response->getBody());
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
        $this->assertStringContainsString('Changelog', (string)$response->getBody());
        $this->assertStringContainsString('Bürgeramt Heerstraße', (string)$response->getBody());
        $this->assertStringContainsString('Sachbearbeiterplatz', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }
}
