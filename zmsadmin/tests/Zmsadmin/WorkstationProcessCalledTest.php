<?php

namespace BO\Zmsadmin\Tests;

class WorkstationProcessCalledTest extends Base
{
    protected $arguments = [
        'id' => 82252
    ];

    protected $parameters = [];

    protected $classname = "WorkstationProcessCalled";

    public function tearDown(): void
    {
        \App::$allowClusterWideCall = true;
        parent::tearDown();
    }

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
                    'function' => 'readGetResult',
                    'url' => '/process/82252/',
                    'response' => $this->readFixture("GET_process_82252_12a2.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/workstation/process/called/',
                    'parameters' => [
                        'allowClusterWideCall' => false
                    ],
                    'response' => $this->readFixture("GET_workstation_with_process_called.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertStringContainsString('<span class="color-blue"><i class="fas fa-info-circle" aria-hidden="true"></i></span> 
 Kundeninformationen', (string)$response->getBody());
        $this->assertStringContainsString('H52452625 (Wartenr. 82252)', (string)$response->getBody());
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
        $this->assertStringContainsString(
            'Sie haben bereits einen Kunden aufgerufen.',
            (string)$response->getBody()
        );
        $this->assertStringContainsString(
            'Bitte schließen Sie den aktuellen Vorgang zuerst ab.',
            (string)$response->getBody()
        );
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
                    'function' => 'readGetResult',
                    'url' => '/process/82252/',
                    'response' => $this->readFixture("GET_process_82252_12a2.json")
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

    public function testConflictWhileProcessingRedirectsWithoutConfirmPanel()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_workstation_with_process_processing.json")
                ]
            ]
        );
        $response = $this->render(['id' => 100044], [], []);
        $this->assertRedirect($response, '/workstation/process/processing/?error=has_called_process');
        $this->assertEquals(302, $response->getStatusCode());
    }
}
