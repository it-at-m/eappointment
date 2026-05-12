<?php

namespace BO\Zmsadmin\Tests;

use BO\Zmsentities\Exception\UserAccountMissingRights;

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
                    'url' => '/roles/2/',
                    'parameters' => [],
                    'response' => '{
                        "$schema": "https://localhost/terminvereinbarung/api/2/",
                        "meta": {
                            "$schema": "https://schema.berlin.de/queuemanagement/metaresult.json",
                            "error": false
                        },
                        "data": {
                            "$schema": "https://schema.berlin.de/queuemanagement/role.json",
                            "id": 2,
                            "name": "audit_viewer",
                            "description": "Innenrevision",
                            "permissions": ["logs"],
                            "assignedUserCount": 0
                        }
                    }'
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/roles/',
                    'parameters' => [],
                    'response' => '{
                        "$schema": "https://localhost/terminvereinbarung/api/2/",
                        "meta": {
                            "$schema": "https://schema.berlin.de/queuemanagement/metaresult.json",
                            "error": false
                        },
                        "data": {
                            "0": {
                                "$schema": "https://schema.berlin.de/queuemanagement/role.json",
                                "id": 1,
                                "name": "system_admin",
                                "description": "Technische Administration",
                                "permissions": ["superuser"],
                                "assignedUserCount": 0
                            },
                            "1": {
                                "$schema": "https://schema.berlin.de/queuemanagement/role.json",
                                "id": 2,
                                "name": "audit_viewer",
                                "description": "Innenrevision",
                                "permissions": ["logs"],
                                "assignedUserCount": 0
                            }
                        }
                    }'
                ],
            ]
        );

        $response = $this->render(
            ['id' => 2],
            [
                'name' => 'system_admin',
                'description' => 'Innenrevision',
                'permissions' => ['logs'],
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
}

