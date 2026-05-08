<?php

namespace BO\Zmsadmin\Tests;

class RoleEditTest extends Base
{
    protected $arguments = [
        'id' => 1
    ];

    protected $parameters = [];

    protected $classname = "RoleEdit";

    public function testRendering()
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
                    'url' => '/roles/1/',
                    'parameters' => [],
                    'response' => $this->readFixture("GET_role_1.json")
                ],
            ]
        );

        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertStringContainsString('Rolle bearbeiten', (string) $response->getBody());
        $this->assertStringContainsString('value="system_admin"', (string) $response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRenderingSave()
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
                    'url' => '/roles/1/',
                    'parameters' => [],
                    'response' => $this->readFixture("GET_role_1.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/roles/1/',
                    'response' => $this->readFixture("GET_role_1.json")
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

        $this->assertRedirect($response, '/roles/1/edit/?success=role_updated');
        $this->assertEquals(302, $response->getStatusCode());
    }
}

