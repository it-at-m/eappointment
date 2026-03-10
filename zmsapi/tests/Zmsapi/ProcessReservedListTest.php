<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;

class ProcessReservedListTest extends Base
{
    protected $classname = "ProcessReservedList";

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->permissions['appointment'] = true;
        $response = $this->render([], [], []);
        $this->assertStringContainsString('process.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testMissingAppointmentPermissionThrows403()
    {
        $this->setWorkstation();
        $this->expectException('\BO\Zmsentities\Exception\UserAccountMissingPermissions');
        $this->render([], [], []);
    }
}
