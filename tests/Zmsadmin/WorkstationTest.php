<?php

namespace BO\Zmsadmin\Tests;

class WorkstationTest extends Base
{
    protected $arguments = [];

    protected $parameters = [];

    protected $classname = "Workstation";

    public function testRendering()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => [
                        'resolveReferences' => 2,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getWorkstation()
                    ],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertStringContainsString('workstation-view', (string)$response->getBody());
        $this->assertStringContainsString('data-selected-date="2016-04-01"', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testWithSelectedDate()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => [
                        'resolveReferences' => 2,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getWorkstation()
                    ],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
            ]
        );
        $response = $this->render($this->arguments, ['date' => '2016-04-04'], []);
        $this->assertStringContainsString('workstation-view', (string)$response->getBody());
        $this->assertStringContainsString('data-selected-date="2016-04-04"', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testLoginFailed()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => [
                        'resolveReferences' => 2,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getWorkstation()
                    ],
                    'response' => $this->readFixture("GET_Workstation_empty.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertRedirect($response, '/');
        $this->assertEquals(302, $response->getStatusCode());
    }
}
