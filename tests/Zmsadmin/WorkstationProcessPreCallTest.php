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
        $this->assertContains('Für ihn ist die folgende Notiz hinterlegt', (string)$response->getBody());
        $this->assertContains('data-exclude="999999,82252"', (string)$response->getBody());
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
        $this->assertContains('1', (string)$response->getBody());
        $this->assertContains('Außerhalb der Öffnungszeiten gebucht!', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRenderingAssignedDepartmentsFailed()
    {
        $this->expectException('BO\Zmsentities\Exception\WorkstationMissingAssignedDepartments');
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
        $this->render($this->arguments, ['exclude' => '999999'], []);
    }
}
