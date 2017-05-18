<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;

class OrganisationListTest extends Base
{
    protected $classname = "OrganisationList";

    public function testRendering()
    {
        $this->setWorkstation();
        $response = $this->render([], [], []);
        $this->assertContains('organisation.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }
}
