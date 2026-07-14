<?php

namespace BO\Zmsbackend\Tests\Config\Api;

use \BO\Slim\Render;

use BO\Zmsbackend\Helper\User;


class ConfigUpdateTest extends \BO\Zmsbackend\Tests\Api\Base
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
        $this->expectException('\BO\Zmsbackend\Config\Exception\ConfigAuthentificationFailed');
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
        $this->expectException('\BO\Zmsbackend\Config\Exception\ConfigAuthentificationFailed');
        $this->expectExceptionCode(401);
        $this->render([], [], []);
    }
}
