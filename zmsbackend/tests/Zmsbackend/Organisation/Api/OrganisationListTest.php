<?php

namespace BO\Zmsbackend\Tests\Organisation\Api;

use BO\Zmsbackend\Helper\User;

class OrganisationListTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "OrganisationList";

    public function testRendering()
    {
        $this->setWorkstation();
        $response = $this->render([], [], []);
        $this->assertStringContainsString('organisation.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }
}
