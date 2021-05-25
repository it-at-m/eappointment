<?php

namespace BO\Zmsapi\Tests;

class UseraccountListTest extends Base
{
    protected $classname = "UseraccountList";

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setRights('useraccount');
        $response = $this->render([], [], []);
        $this->assertStringContainsString('useraccount.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testRights()
    {
        $this->setWorkstation()->getUseraccount()->setRights('useraccount');
        $response = $this->render([], ['right' => 'superuser'], []);
        $this->assertStringContainsString('useraccount.json', (string)$response->getBody());
        $this->assertStringNotContainsString('"superuser":"0"', (string)$response->getBody());
        $this->assertStringContainsString('"superuser":"1"', (string)$response->getBody());
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
