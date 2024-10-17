<?php

namespace BO\Zmsadmin\Tests;

class UseraccountByDepartmentTest extends Base
{
    protected $arguments = [
        'id' => 74
    ];

    protected $parameters = [];

    protected $classname = "UseraccountByDepartment";

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
                    'url' => '/department/74/',
                    'response' => $this->readFixture("GET_department_74.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/department/74/useraccount/',
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture("GET_useraccountlist_department_74.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/department/74/workstation/',
                    'parameters' => ['resolveReferences' => 0],
                    'response' => $this->readFixture("GET_workstationlist_department_74.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/owner/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_ownerlist.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/',
                    'parameters' => ['resolveReferences' => 0],
                    'response' => $this->readFixture("GET_scope_list.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertStringContainsString('useraccount-list', (string)$response->getBody());
        $this->assertStringContainsString('/useraccount/testuser/', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }
}
