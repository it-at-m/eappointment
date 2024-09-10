<?php

namespace BO\Zmsadmin\Tests;

class WorkstationProcessPreCallTest extends Base
{
    protected $arguments = [
        'id' => 82252
    ];

    protected $parameters = [];

    protected $classname = "WorkstationProcessPreCall";

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
                    'url' => '/process/82252/',
                    'response' => $this->readFixture("GET_process_82252_12a2.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, ['exclude' => '999999'], []);
        $this->assertStringContainsString('Möchten Sie den Kunden aufrufen?', (string)$response->getBody());
        $this->assertStringContainsString('data-exclude="999999,82252"', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testEmptyName()
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
                    'url' => '/process/82252/',
                    'response' => $this->readFixture("GET_process_spontankunde_empty_name.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, [], []);
        $this->assertStringContainsString('1', (string)$response->getBody());
        $this->assertStringContainsString('Außerhalb der Öffnungszeiten gebucht!', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRenderingAssignedDepartmentsFailed()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_Workstation_empty.json")
                ]
                ,
                [
                    'function' => 'readGetResult',
                    'url' => '/process/82252/',
                    'response' => $this->readFixture("GET_process_spontankunde_empty_name.json")
                ]            ]
        );
        $this->render($this->arguments, ['exclude' => '999999'], []);
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
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/process/82252/',
                    'response' => $this->readFixture("GET_process_82252_12a2.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, [
            'exclude' => 999999
        ], []);
        $this->assertStringContainsString('Dieser Arbeitsplatz hat schon einen Abholer aufgerufen.', (string)$response->getBody());
        $this->assertStringContainsString('Zur Abholerverwaltung', (string)$response->getBody());
        $this->assertStringNotContainsString('client-precall_button-success', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRenderingAlreadyCalledProcess()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_workstation_with_process.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/process/100044/',
                    'response' => $this->readFixture("GET_process_100044_57c2.json")
                ]
            ]
        );
        $response = $this->render(['id' => 100044], [], []);
        $this->assertStringContainsString('Dieser Arbeitsplatz hat schon einen Vorgang aufgerufen.', (string)$response->getBody());
        $this->assertStringContainsString('<span class="color-blue"><i class="fas fa-info-circle" aria-hidden="true"></i></span> 
 Kundeninformationen', (string)$response->getBody());
        $this->assertStringContainsString('client-precall_button-success', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRenderingAlreadyCalledProcessRedirect()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_workstation_with_process_called.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/process/100044/',
                    'response' => $this->readFixture("GET_process_100044_57c2.json")
                ]
            ]
        );
        $response = $this->render(['id' => 100044], [], []);
        $this->assertRedirect($response, '/workstation/process/82252/called/?error=has_called_process');
        $this->assertEquals(302, $response->getStatusCode());
    }
}
