<?php

namespace BO\Zmsbackend\Tests\Owner\Api;

use BO\Zmsbackend\Helper\User;

class OwnerListTest extends \BO\Zmsbackend\Tests\Api\Base
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
