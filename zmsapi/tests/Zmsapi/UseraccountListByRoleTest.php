<?php

namespace BO\Zmsapi\Tests;

class UseraccountListByRoleTest extends Base
{
    protected $classname = 'UseraccountListByRole';

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('superuser', 'useraccount');
        $response = $this->render(['level' => 10], [], []);
        $this->assertStringContainsString('testuser', (string) $response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testMissingRights()
    {
        $this->setWorkstation();
        $this->expectException('BO\Zmsentities\Exception\UserAccountMissingRights');
        $this->expectExceptionCode(403);
        $this->render(['level' => 50], [], []);
    }
}
