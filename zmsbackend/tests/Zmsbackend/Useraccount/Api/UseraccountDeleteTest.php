<?php

namespace BO\Zmsbackend\Tests\Useraccount\Api;

use BO\Zmsbackend\Helper\User;

class UseraccountDeleteTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "UseraccountDelete";

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('useraccount');
        $this->setDepartment(74);
        $response = $this->render(['loginname' => 'testadmin'], [], []);
        $this->assertStringContainsString('useraccount.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testNotFound()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('useraccount');
        $this->setDepartment(74);
        $this->expectException('\BO\Zmsbackend\Useraccount\Exception\UseraccountNotFound');
        $this->expectExceptionCode(404);
        $this->render(['loginname' => 'test'], [], []);
    }

    public function testMissingRights()
    {
        $this->setWorkstation();
        $this->expectException('BO\Zmsentities\Exception\UserAccountMissingRights');
        $this->expectExceptionCode(403);
        $this->render(['loginname' => 'testadmin'], [], []);
    }
}
