<?php

namespace BO\Zmsadmin\Tests;

use BO\Zmsentities\Exception\UserAccountMissingRights;
use BO\Zmsentities\Exception\UserAccountAccessRightsFailed;

class UseraccountListByRoleTest extends Base
{
    protected $arguments = [
        'roleName' => 'agent_queue',
    ];

    protected $parameters = [];

    protected $classname = 'UseraccountListByRole';

    public function testRendering()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture('GET_Workstation_Resolved2.json'),
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/owner/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture('GET_ownerlist.json'),
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/role/agent_queue/useraccount/',
                    'parameters' => ['resolveReferences' => 0],
                    'response' => $this->readFixture('GET_useraccountlist_role_agent_queue.json'),
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/roles/',
                    'parameters' => [],
                    'response' => $this->readFixture('GET_rolelist.json'),
                ],
            ]
        );

        $response = $this->render($this->arguments, $this->parameters, []);
        $body = (string) $response->getBody();

        $this->assertStringContainsString('useraccount-list', $body);
        $this->assertStringContainsString('testadmin', $body);
        $this->assertStringContainsString('Agenten-Queue Rolle', $body);
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

    public function testSuperuserOnlyRoleRequiresSuperuser()
    {
        $this->arguments = ['roleName' => 'system_admin'];

        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture('GET_Workstation_Resolved1.json'),
                ],
            ]
        );

        $this->expectException(UserAccountAccessRightsFailed::class);
        $this->render($this->arguments, $this->parameters, []);
    }
}

