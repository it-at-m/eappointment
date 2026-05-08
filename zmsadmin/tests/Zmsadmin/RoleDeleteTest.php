<?php

namespace BO\Zmsadmin\Tests;

use BO\Zmsentities\Exception\UserAccountMissingRights;

class RoleDeleteTest extends Base
{
    protected $arguments = [
        'id' => 1,
    ];

    protected $parameters = [];

    protected $classname = "RoleDelete";

    public function testRendering()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture("GET_Workstation_Superuser_Resolved1.json"),
                ],
                [
                    'function' => 'readDeleteResult',
                    'url' => '/roles/1/',
                    'parameters' => [],
                    'response' => $this->readFixture("GET_role_1.json"),
                ],
            ]
        );

        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertEquals(204, $response->getStatusCode());
    }

    public function testMissingSuperuserRights()
    {
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

        $this->expectException(UserAccountMissingRights::class);
        $this->render($this->arguments, $this->parameters, []);
    }
}