<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;

class MailListTest extends Base
{
    protected $classname = "MailList";

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('superuser');
        $response = $this->render([], [], []);
        $this->assertStringContainsString('"error":false', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testMissingRights()
    {
        $this->setWorkstation();
        $this->expectException('\BO\Zmsentities\Exception\UserAccountMissingRights');
        $this->expectExceptionCode(403);
        $this->render([], [], []);
    }
}
