<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;

class DepartmentListTest extends Base
{
    protected $classname = "DepartmentList";

    public function testRendering()
    {
        $this->setWorkstation();
        User::$workstation->useraccount->setRights('department');
        $response = $this->render([], [], []);
        $this->assertContains('department.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testMissingRights()
    {
        $this->expectException('\BO\Zmsentities\Exception\UserAccountMissingLogin');
        $this->expectExceptionCode(401);
        $this->render([], [], []);
    }
}
