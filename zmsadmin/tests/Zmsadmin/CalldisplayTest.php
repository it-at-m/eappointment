<?php

namespace BO\Zmsadmin\Tests;

class CalldisplayTest extends Base
{
    protected $arguments = [];

    protected $parameters = [];

    protected $classname = "Calldisplay";

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
                    'url' => '/config/',
                    'response' => $this->readFixture("GET_config.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/organisation/',
                    'parameters' => ['resolveReferences' => 3],
                    'response' => $this->readFixture("GET_organisation_71_resolved3.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertStringContainsString(
            'Aufrufanzeige Standortauswahl',
            (string)$response->getBody()
        );
        $this->assertStringContainsString(
            'Charlottenburg-Wilmersdorf',
            (string)$response->getBody()
        );
        $this->assertStringContainsString('Bürgeramt Heerstraße', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }
}
