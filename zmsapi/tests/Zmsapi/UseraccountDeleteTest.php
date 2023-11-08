<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;

class UseraccountDeleteTest extends Base
{
    protected $classname = "UseraccountDelete";

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setRights('useraccount');
        $this->setDepartment(74);
        $response = $this->render(['loginname' => 'testadmin'], [], []);
        $this->assertStringContainsString('useraccount.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testNotFound()
    {
        $this->setWorkstation()->getUseraccount()->setRights('useraccount');
        $this->setDepartment(74);
        $this->expectException('\BO\Zmsapi\Exception\Useraccount\UseraccountNotFound');
        $this->expectExceptionCode(404);
        $this->render(['loginname' => 'test'], [], []);
    }
}
