<?php

namespace BO\Zmsadmin\Tests;

use BO\Zmsentities\Exception\UserAccountMissingRights;

class RoleAddTest extends Base
{
    protected $arguments = [];

    protected $parameters = [];

    protected $classname = "RoleAdd";

    public function testRendering()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 1],
                    'function' => 'readGetResult',
                    'url' => '/permissions/',
                    'parameters' => [],
                    'response' => $this->readFixture("GET_permissionlist.json")
                ],
            ]
        );

        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertStringContainsString('Rolle hinzufügen', (string) $response->getBody());
        $this->assertStringContainsString('perm_superuser', (string) $response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRenderingSave()
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
                    'url' => '/permissions/',
                    'parameters' => [],
                    'response' => $this->readFixture("GET_permissionlist.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/roles/',
                    'parameters' => [],
                    'response' => $this->readFixture("GET_rolelist.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/roles/',
                    'response' => $this->readFixture("GET_role_1.json")
                ],
            ]
        );

        $response = $this->render(
            $this->arguments,
            [
                'name' => 'test_role_add',
                'description' => 'Test Role Add',
                'permissions' => ['superuser'],
            ],
            [],
            'POST'
        );

        $this->assertRedirect($response, '/roles/1/edit/?success=role_added');
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testRenderingSaveDuplicateName()
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
                    'url' => '/permissions/',
                    'parameters' => [],
                    'response' => $this->readFixture("GET_permissionlist.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/roles/',
                    'parameters' => [],
                    'response' => $this->readFixture("GET_rolelist.json")
                ],
            ]
        );

        $response = $this->render(
            $this->arguments,
            [
                'name' => 'system_admin',
                'description' => 'Technische Administration',
                'permissions' => ['superuser'],
            ],
            [],
            'POST'
        );

        $this->assertStringContainsString('Eine Rolle mit diesem Namen existiert bereits.', (string) $response->getBody());
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

    public function testRenderingSaveDuplicateName()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/permissions/',
                    'parameters' => [],
                    'response' => $this->readFixture("GET_permissionlist.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/roles/',
                    'parameters' => [],
                    'response' => $this->readFixture("GET_rolelist.json")
                ],
            ]
        );

        $response = $this->render(
            $this->arguments,
            [
                'name' => 'system_admin',
                'description' => 'Technische Administration',
                'permissions' => ['superuser'],
            ],
            [],
            'POST'
        );

        $this->assertStringContainsString('Eine Rolle mit diesem Namen existiert bereits.', (string) $response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }
}

