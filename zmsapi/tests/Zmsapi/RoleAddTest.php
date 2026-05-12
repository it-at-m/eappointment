<?php

namespace BO\Zmsapi\Tests;

class RoleAddTest extends Base
{
    protected $classname = "RoleAdd";

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('useraccount');

        $response = $this->render([], [
            '__body' => json_encode([
                'name' => 'test_role_api_add',
                'description' => 'Test Role',
                'permissions' => ['superuser'],
                'id' => 999,
                'assignedUserCount' => 123,
            ]),
        ], []);

        $this->assertStringContainsString('role.json', (string) $response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testInputInvalid()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('useraccount');

        $this->expectException('\BO\Zmsentities\Exception\SchemaValidation');
        $this->expectExceptionCode(400);

        $this->render([], [
            '__body' => '{"extraField":0}',
        ], []);
    }
}

