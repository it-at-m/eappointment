<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;

class WarehouseSubjectListGetTest extends Base
{
    protected $classname = "WarehouseSubjectListGet";

    public function testRendering()
    {
        $workstation = $this->setWorkstation();
        $workstation->getUseraccount()->setRights('scope');
        $response = $this->render([], [], []);
        $this->assertStringContainsString('exchange.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testNoRights()
    {
        $workstation = $this->setWorkstation();
        $workstation->getUseraccount()->setRights('basic');
        $this->expectException('\BO\Zmsentities\Exception\UserAccountMissingRights');
        $this->render([], [], []);
    }
}
