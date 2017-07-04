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
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/provider/dldb/122217/',
                    'response' => $this->readFixture("GET_provider_122217.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/provider/dldb/122217/request/',
                    'response' => $this->readFixture("GET_provider_122217_requestlist.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertContains('workstation-view', (string)$response->getBody());
        $this->assertContains('data-selected-date="2016-04-01"', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testLoginFailed()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_Workstation_empty.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertRedirect($response, '/');
        $this->assertEquals(302, $response->getStatusCode());
    }
}
