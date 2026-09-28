<?php

namespace BO\Zmsbackend\Tests\Role\Api;

class RoleListGetTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "RoleListGet";

    #[\Override]
    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('useraccount');

        $response = $this->render([], [], []);
        $this->assertStringContainsString('role.json', (string) $response->getBody());
        $this->assertSame(200, $response->getStatusCode());
    }
}

