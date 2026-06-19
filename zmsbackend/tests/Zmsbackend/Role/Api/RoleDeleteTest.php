<?php

namespace BO\Zmsbackend\Tests\Role\Api;

use BO\Zmsbackend\Role\Service\Role as RoleRepository;
use BO\Zmsentities\Role as RoleEntity;

class RoleDeleteTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "RoleDelete";

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('superuser');

        $created = (new RoleRepository())->addRole(new RoleEntity([
            'name' => 'test_role_api_delete',
            'description' => 'To delete',
            'permissions' => ['superuser'],
        ]));

        $response = $this->render(['id' => $created->id], [], []);
        $this->assertStringContainsString('role.json', (string) $response->getBody());
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testNotFound()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('superuser');

        $this->expectException('\BO\Zmsbackend\Role\Exception\RoleDoesNotExist');
        $this->expectExceptionCode(404);

        $this->render(['id' => 0], [], []);
    }
}
