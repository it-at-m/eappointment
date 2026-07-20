<?php

namespace BO\Zmsadmin\Tests;

use BO\Zmsentities\Exception\UserAccountMissingRights;

class UseraccountTest extends Base
{
    protected $arguments = [];

    protected $parameters = [];

    protected $classname = "UseraccountList";

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
                    'function' => 'readGetResult',
                    'url' => '/useraccount/',
                    'parameters' => ['resolveReferences' => 0],
                    'response' => $this->readFixture("GET_useraccountlist_superuser.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/owner/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_ownerlist.json")
                ],
                // Neu: Rollenliste für roleMap
                [
                    'function' => 'readGetResult',
                    'url' => '/roles/',
                    'parameters' => [],
                    'response' => $this->readFixture("GET_rolelist.json"),
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $body = (string)$response->getBody();

        $this->assertStringContainsString('Gesamtnutzerliste', $body);
        $this->assertStringContainsString('berlinonline', $body);
        $this->assertStringContainsString('Rolle', $body);
        $this->assertStringContainsString('Technische Administration', $body);
        $this->assertStringContainsString('Agenten-Queue Rolle', $body);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRenderingByDepartment()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture("GET_workstation_with_process.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_workstation_with_process.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/department/74/useraccount/',
                    'parameters' => ['resolveReferences' => 0],
                    'response' => $this->readFixture("GET_useraccountlist_department_74.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/owner/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_ownerlist.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/roles/',
                    'parameters' => [],
                    'response' => $this->readFixture("GET_rolelist.json"),
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $body = (string)$response->getBody();

        $this->assertStringContainsString('Charlottenburg-Wilmersdorf', $body);
        $this->assertStringContainsString('Rolle', $body);
        $this->assertStringContainsString('Agenten-Queue Rolle', $body);
        $this->assertStringContainsString('testuser', $body);
        $this->assertStringNotContainsString('/users/berlinonline', $body);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testMissingUseraccountRights()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture('GET_Workstation_Resolved1_No_Useraccount_Permission.json'),
                ],
            ]
        );

        $this->expectException(UserAccountMissingRights::class);
        $this->render($this->arguments, $this->parameters, []);
    }
}

