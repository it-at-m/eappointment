<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;

class WarehouseSubjectListGetTest extends Base
{
    protected $classname = "WarehouseSubjectListGet";

    public function testRendering()
    {
        $this->setWorkstation();
        User::$workstation->useraccount->setRights('organisation');
        $response = $this->render([], [], []);
        $this->assertContains('exchange.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testNoRights()
    {
        $this->setWorkstation();
        User::$workstation->useraccount->setRights('department');
        $this->expectException('\BO\Zmsentities\Exception\UserAccountMissingRights');
        $this->render([], [], []);
    }
}
