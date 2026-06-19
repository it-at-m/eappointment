<?php

namespace BO\Zmsbackend\Tests\Warehouse\Api;

use BO\Zmsbackend\Helper\User;

class WarehouseSubjectListGetTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "WarehouseSubjectListGet";

    public function testRendering()
    {
        $workstation = $this->setWorkstation();
        $workstation->getUseraccount()->setPermissions('statistic');
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
