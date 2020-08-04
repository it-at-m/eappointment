<?php

namespace BO\Zmsadmin\Tests;

class WorkstationProcessCalledTest extends Base
{
    protected $arguments = [
        'id' => 82252
    ];

    protected $parameters = [];

    protected $classname = "WorkstationProcessCalled";

    public function testRendering()
    {
        \App::$allowClusterWideCall = false;
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/workstation/process/called/',
                    'parameters' => ['allowClusterWideCall' => false],
                    'response' => $this->readFixture("GET_workstation_with_process_called.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertContains('Kundeninformationen', (string)$response->getBody());
        $this->assertContains('H52452625 (Wartenr. 82252)', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRenderingAlreadyCalledProcessWithExcludes()
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
        $response = $this->render(['id' => 161275], [
            'exclude' => 82252
        ], []);
        $this->assertContains(
            'Dieser Arbeitsplatz hat schon einen Vorgang aufgerufen. Dieser wird weiterhin verwendet.',
            (string)$response->getBody()
        );
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRenderingAlreadyCalledPickup()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_workstation_with_process_pickup.json")
                ]
            ]
        );
        $response = $this->render(['id' => 161275], [
            'exclude' => 82252
        ], []);
        $this->assertContains('Dieser Arbeitsplatz hat schon einen Abholer aufgerufen.', (string)$response->getBody());
        $this->assertContains('Zur Abholerverwaltung', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testWithStatusProcessing()
    {
        \App::$allowClusterWideCall = false;
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/workstation/process/called/',
                    'parameters' => ['allowClusterWideCall' => false],
                    'response' => $this->readFixture("GET_workstation_with_process_processing.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertRedirect($response, '/workstation/process/processing/?');
        $this->assertEquals(302, $response->getStatusCode());
    }
}
