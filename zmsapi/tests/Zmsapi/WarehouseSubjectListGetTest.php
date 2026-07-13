<?php

namespace BO\Zmsapi\Tests;

class WarehouseSubjectListGetTest extends Base
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

    public function testMissingStatisticPermission()
    {
        $workstation = $this->setWorkstation();
        $this->expectException('\BO\Zmsentities\Exception\UserAccountMissingRights');
        $this->render([], [], []);
    }
}
