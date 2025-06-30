<?php

namespace BO\Zmsadmin\Tests;

use BO\Zmsentities\Exception\UserAccountMissingRights;

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
                    'parameters'=> ['resolveReferences' => 3],
                    'response'  => $this->readFixture("GET_Workstation_Resolved2.json")
                ]
            ]
        );

        $response = $this->render([], [], []);

        $this->assertStringContainsString('Wochenkalender', (string)$response->getBody());
        $this->assertStringContainsString('overall-calendar', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testScopeRequired()
    {
        $this->setApiCalls(
            [
                [
                    'function'  => 'readGetResult',
                    'url'       => '/workstation/',
                    'parameters'=> ['resolveReferences' => 3],
                    'response'  => $this->readFixture("GET_workstation_basic.json")
                ]
            ]
        );

        $this->expectException(\BO\Zmsentities\Exception\UserAccountMissingRights::class);
        $this->render([], [], []);
    }

    public function testWithoutScope()
    {
        $this->setApiCalls(
            [
                [
                    'function'  => 'readGetResult',
                    'url'       => '/workstation/',
                    'parameters'=> ['resolveReferences' => 3],
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
                    'parameters'=> ['resolveReferences' => 3],
                    'response'  => $this->readFixture("GET_Workstation_Resolved2.json")
                ]
            ]
        );

        $response = $this->render([], [], []);

        $this->assertStringContainsString('Wochenkalender', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }
}