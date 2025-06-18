<?php

namespace BO\Zmsadmin\Tests;

use BO\Zmsadmin\Exception\NotAllowed;

class OverallCalendarTest extends Base
{
    protected $arguments = [];
    protected $parameters = [];
    protected $classname = "OverallCalendar";

    public function testRendering()
    {
        $this->setApiCalls(
            [
                [
                    'function'  => 'readGetResult',
                    'url'       => '/workstation/',
                    'parameters'=> ['resolveReferences' => 1],
                    'response'  => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function'  => 'readGetResult',
                    'url'       => '/scope/141/department/',
                    'parameters'=> ['resolveReferences' => 2],
                    'response'  => $this->readFixture("GET_department_74.json")
                ]
            ]
        );

        $response = $this->render([], [], []);

        $this->assertStringContainsString('Wochenkalender', (string)$response->getBody());
        $this->assertStringContainsString('overall-calendar', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testSuperuserRequired()
    {
        $this->setApiCalls(
            [
                [
                    'function'  => 'readGetResult',
                    'url'       => '/workstation/',
                    'parameters'=> ['resolveReferences' => 1],
                    'response'  => $this->readFixture("GET_Workstation_Resolved1.json")
                ]
            ]
        );

        $this->expectException(NotAllowed::class);

        $this->render([], [], []);
    }

    public function testWithoutScope()
    {
        $this->setApiCalls(
            [
                [
                    'function'  => 'readGetResult',
                    'url'       => '/workstation/',
                    'parameters'=> ['resolveReferences' => 1],
                    'response'  => $this->readFixture("GET_Workstation_NoScope.json")
                ]
            ]
        );

        $response = $this->render([], [], []);

        $this->assertStringContainsString('Wochenkalender', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testWithDepartmentNull()
    {
        $this->setApiCalls(
            [
                [
                    'function'  => 'readGetResult',
                    'url'       => '/workstation/',
                    'parameters'=> ['resolveReferences' => 1],
                    'response'  => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function'  => 'readGetResult',
                    'url'       => '/scope/141/department/',
                    'parameters'=> ['resolveReferences' => 2],
                    'response'  => null
                ]
            ]
        );

        $response = $this->render([], [], []);

        $this->assertStringContainsString('Wochenkalender', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }
}