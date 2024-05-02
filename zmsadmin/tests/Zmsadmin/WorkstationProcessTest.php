<?php

namespace BO\Zmsadmin\Tests;

class WorkstationProcessTest extends Base
{
    protected $arguments = [];

    protected $parameters = [];

    protected $classname = "WorkstationProcess";

    public function testRendering()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertStringContainsString('Aufruf nächster Kunde', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRenderingWithCalledProcess()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_workstation_with_process_called.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, [$this->parameters], []);
        $this->assertRedirect($response, '/workstation/process/82252/called/');
    }

    public function testRenderingWithProcessingProcess()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_workstation_with_process.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, [$this->parameters], []);
        $this->assertStringContainsString('<span class="color-blue"><i class="fas fa-info-circle" aria-hidden="true"></i></span> 
 Kundeninformationen', (string)$response->getBody());
        $this->assertStringContainsString('Personalausweis beantragen', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRenderingWithClusterEnabled()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_Workstation_clusterEnabled.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertStringContainsString('Aufruf nächster Kunde', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }
}
