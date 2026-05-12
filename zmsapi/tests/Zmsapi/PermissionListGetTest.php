<?php

namespace BO\Zmsapi\Tests;

class PermissionListGetTest extends Base
{
    protected $classname = "PermissionListGet";

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('useraccount');

        $response = $this->render([], [], []);
        $this->assertStringContainsString('permission.json', (string) $response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }
}

