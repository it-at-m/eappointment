<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;

class OwnerListTest extends Base
{
    protected $classname = "OwnerList";

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setRights('superuser');
        $response = $this->render([], [], []);
        $this->assertStringContainsString('owner.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }
}
