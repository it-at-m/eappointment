<?php

namespace BO\Zmsbackend\Tests\Role\Api;

use BO\Zmsbackend\Role\Service\Role as RoleRepository;
use BO\Zmsentities\Role as RoleEntity;

class RoleUpdateTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "RoleUpdate";

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('superuser');

        $created = (new RoleRepository())->addRole(new RoleEntity([
            'name' => 'test_role_api_update_before',
            'description' => 'Before',
            'permissions' => ['superuser'],
        ]));

        $response = $this->render(['id' => $created->id], [
            '__body' => json_encode([
                'name' => 'test_role_api_update_after',
                'description' => 'After',
                'permissions' => ['superuser'],
                'id' => 123,
                'assignedUserCount' => 456,
            ]),
        ], []);

        $this->assertStringContainsString('role.json', (string) $response->getBody());
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testNotFound()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('superuser');

        $this->expectException('\BO\Zmsbackend\Role\Exception\RoleDoesNotExist');
        $this->expectExceptionCode(404);

        $this->render(['id' => 0], [
            '__body' => json_encode([
                'name' => 'test_role_api_update_missing',
                'description' => 'Test Role',
                'permissions' => ['superuser'],
            ]),
        ], []);
    }

    public function testDescriptionRequired()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('superuser');

        $created = (new RoleRepository())->addRole(new RoleEntity([
            'name' => 'test_role_api_update_description_before',
            'description' => 'Before',
            'permissions' => ['superuser'],
        ]));

        $this->expectException('\BO\Zmsentities\Exception\SchemaValidation');
        $this->expectExceptionCode(400);

        $this->render(['id' => $created->id], [
            '__body' => json_encode([
                'name' => 'test_role_api_update_description_after',
                'description' => '',
                'permissions' => ['superuser'],
            ]),
        ], []);
    }
}
