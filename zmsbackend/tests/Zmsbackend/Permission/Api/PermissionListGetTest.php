<?php

namespace BO\Zmsbackend\Tests\Permission\Api;

class PermissionListGetTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "PermissionListGet";

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('superuser');

        $response = $this->render([], [], []);
        $this->assertStringContainsString('permission.json', (string) $response->getBody());
        $this->assertSame(200, $response->getStatusCode());
    }
}
