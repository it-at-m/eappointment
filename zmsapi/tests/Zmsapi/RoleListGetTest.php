<?php

namespace BO\Zmsapi\Tests;

class RoleListGetTest extends Base
{
    protected $classname = "RoleListGet";

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('useraccount');

        $response = $this->render([], [], []);
        $this->assertStringContainsString('role.json', (string) $response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }
}

