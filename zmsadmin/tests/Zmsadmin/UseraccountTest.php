<?php

namespace BO\Zmsadmin\Tests;

class UseraccountTest extends Base
{
    protected $arguments = [];

    protected $parameters = [];

    protected $classname = "Useraccount";

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
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertStringContainsString('Gesamtnutzerliste', (string)$response->getBody());
        $this->assertStringContainsString('/useraccount/berlinonline', (string)$response->getBody());
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
                    'response' => $this->readFixture("GET_useraccountlist_department_74.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/owner/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_ownerlist.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertStringContainsString('Charlottenburg-Wilmersdorf', (string)$response->getBody());
        $this->assertStringNotContainsString('/useraccount/berlinonline', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }
}
