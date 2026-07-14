<?php

namespace BO\Zmsbackend\Tests\Useraccount\Api;

class UseraccountListTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = 'UseraccountList';

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('useraccount');
        $this->setDepartment(74);
        $response = $this->render([], [], []);
        $this->assertStringContainsString('testuser', (string) $response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testMissingRights()
    {
        $this->setWorkstation();
        $this->expectException('BO\Zmsentities\Exception\UserAccountMissingRights');
        $this->expectExceptionCode(403);
        $this->render([], [], []);
    }
}
