<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsdb\Role as RoleRepository;
use BO\Zmsentities\Role as RoleEntity;

class RoleDeleteTest extends Base
{
    protected $classname = "RoleDelete";

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setRights('useraccount');

        $created = (new RoleRepository())->addRole(new RoleEntity([
            'name' => 'test_role_api_delete',
            'description' => 'To delete',
            'permissions' => ['superuser'],
        ]));

        $response = $this->render(['id' => $created->id], [], []);
        $this->assertStringContainsString('role.json', (string) $response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testNotFound()
    {
        $this->setWorkstation()->getUseraccount()->setRights('useraccount');

        $this->expectException('\BO\Zmsapi\Exception\Role\RoleNotFound');
        $this->expectExceptionCode(404);

        $this->render(['id' => 0], [], []);
    }
}

