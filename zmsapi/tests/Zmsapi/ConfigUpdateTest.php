<?php

namespace BO\Zmsapi\Tests;


class ConfigUpdateTest extends Base
{
    protected $classname = "ConfigUpdate";

    public function testRendering()
    {
        $response = $this->render([], [
            '__header' => array(
                'X-Token' => 'secure-token'
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
        $this->setWorkstation()->getUseraccount()->setPermissions('superuser');
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
