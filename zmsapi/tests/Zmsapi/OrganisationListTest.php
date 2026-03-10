<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;

class OrganisationListTest extends Base
{
    protected $classname = "OrganisationList";

    public function testRendering()
    {
        $workstation = $this->setWorkstation();
        $workstation->getUseraccount()->permissions['counter'] = true;
        $response = $this->render([], [], []);
        $this->assertStringContainsString('organisation.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testMissingCounterPermissionThrows403()
    {
        $this->setWorkstation();
        $this->expectException('\BO\Zmsentities\Exception\UserAccountMissingPermissions');
        $this->render([], [], []);
    }
}
