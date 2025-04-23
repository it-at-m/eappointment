<?php

namespace BO\Zmsapi\Tests;

class UseraccountListTest extends Base
{
    protected $classname = "UseraccountList";

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setRights('useraccount');
        $response = $this->render([], [], []);
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testMissingLogin()
    {
        $this->expectException('BO\Zmsentities\Exception\UserAccountMissingLogin');
        $this->expectExceptionCode(401);
        $this->render([], [], []);
    }

    public function testMissingRights()
    {
        $this->setWorkstation();
        $this->expectException('BO\Zmsentities\Exception\UserAccountMissingRights');
        $this->expectExceptionCode(403);
        $this->render([], [], []);
    }
}
