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
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/workstation/process/called/',
                    'response' => $this->readFixture("GET_workstation_with_process.json")
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
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/workstation/process/called/',
                    'response' => $this->readFixture("GET_workstation_basic.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, [
            'exclude' => 82252
        ], []);
        $this->assertContains(
            'Dieser Arbeitsplatz hat schon einen Termin aufgerufen. Dieser wird weiterhin verwendet.',
            (string)$response->getBody()
        );
        $this->assertContains('message--error', (string)$response->getBody());
        $this->assertContains('data-exclude="82252,9999999"', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }
}
