<?php

namespace BO\Zmsapi\Tests;

use \BO\Slim\Render;

use BO\Zmsapi\Helper\User;

class ConfigUpdateTest extends Base
{
    protected $classname = "ConfigUpdate";

    public function testRendering()
    {
        $response = $this->render([], [
            '__header' => array(
                'X-Token' => 'a9b215f1-e460-490c-8a0b-6d42c274d5e4'
            ),
            '__body' => '{
                  "cron": {
                    "sendMailReminder":"dev,stage"
                  }
              }'
        ], []);

        $this->assertStringContainsString('"sendMailReminder":"dev,stage"', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testWithSuperuser()
    {
        $this->setWorkstation();
        User::$workstation->useraccount->setRights('superuser');
        $response = $this->render([], [
            '__body' => '{
                  "cron": {
                    "sendMailReminder":"dev,stage"
                  }
              }'
        ], []);

        $this->assertStringContainsString('"sendMailReminder":"dev,stage"', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testWithoutAccess()
    {
        $this->expectException('\BO\Zmsapi\Exception\Config\ConfigAuthentificationFailed');
        $this->expectExceptionCode(401);
        $this->setWorkstation();
        User::$workstation->useraccount->setRights('basic');
        $response = $this->render([], [
            '__body' => '{
                  "cron": {
                    "sendMailReminder":"dev,stage"
                  }
              }'
        ], []);
    }

    public function testAuthentificationFailed()
    {
        $this->expectException('\BO\Zmsapi\Exception\Config\ConfigAuthentificationFailed');
        $this->expectExceptionCode(401);
        $this->render([], [], []);
    }
}
