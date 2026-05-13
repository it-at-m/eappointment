<?php

namespace BO\Zmsadmin\Tests;

use BO\Zmsentities\Exception\UserAccountMissingRights;

class RolesTest extends Base
{
    protected $arguments = [];

    protected $parameters = [];

    protected $classname = 'Roles';

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
                    'url' => '/roles/',
                    'parameters' => [],
                    'response' => $this->readFixture('GET_rolelist.json'),
                ],
            ]
        );

        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertStringContainsString('Rollen', (string) $response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
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
