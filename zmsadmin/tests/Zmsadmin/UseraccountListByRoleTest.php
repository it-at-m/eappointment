<?php

namespace BO\Zmsadmin\Tests;

use BO\Zmsentities\Exception\UserAccountMissingRights;

class UseraccountListByRoleTest extends Base
{
    protected $arguments = [
        'level' => 50,
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
                    'url' => '/role/50/useraccount/',
                    'response' => $this->readFixture('GET_useraccountlist_role_50.json'),
                ],
            ]
        );

        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertStringContainsString('useraccount-list', (string) $response->getBody());
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
