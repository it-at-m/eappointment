<?php

namespace BO\Zmsadmin\Tests;

use BO\Zmsentities\Exception\UserAccountMissingRights;

class UseraccountByDepartmentTest extends Base
{
    protected $arguments = [
        'id' => 74
    ];

    protected $parameters = [];

    protected $classname = "UseraccountListByDepartment";

    public function testRendering()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture("GET_Workstation_Resolved1.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/department/74/',
                    'parameters' => ['resolveReferences' => 0],
                    'response' => $this->readFixture("GET_department_74.json")
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
        $this->assertStringContainsString('useraccount-list', (string)$response->getBody());
        $this->assertStringContainsString('/users/testuser/', (string)$response->getBody());
        $this->assertStringContainsString('Rolle', (string)$response->getBody());
        $this->assertStringContainsString('Agenten-Queue Rolle', (string)$response->getBody());
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

