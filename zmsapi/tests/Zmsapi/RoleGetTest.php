<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsdb\Role as RoleRepository;
use BO\Zmsentities\Role as RoleEntity;

class RoleGetTest extends Base
{
    protected $classname = "RoleGet";

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('useraccount');

        $created = (new RoleRepository())->addRole(new RoleEntity([
            'name' => 'test_role_api_get',
            'description' => 'Test Role',
            'permissions' => ['superuser'],
        ]));

        $this->assertNotNull($created);

        $response = $this->render(['id' => $created->id], [], []);
        $this->assertStringContainsString('role.json', (string) $response->getBody());
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testNotFound()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('useraccount');

        $this->expectException('\BO\Zmsapi\Exception\Role\RoleDoesNotExist');
        $this->expectExceptionCode(404);

        $this->render(['id' => 0], [], []);
    }
}

