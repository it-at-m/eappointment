<?php

namespace BO\Zmsapi\Tests;

use \BO\Slim\Render;

class ConfigGetTest extends Base
{
    protected $classname = "ConfigGet";

    public function testRendering()
    {
        $response = $this->render([], [
            '__header' => array(
                'X-Token' => 'a9b215f1-e460-490c-8a0b-6d42c274d5e4'
            )
        ], []);
        $this->assertStringContainsString('config.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testConfigByXAuthKey()
    {
        $this->setWorkstation();
        $response = $this->render([], [], []);
        $this->assertStringContainsString('config.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testAuthentificationFailed()
    {
        $this->expectException('\BO\Zmsapi\Exception\Config\ConfigAuthentificationFailed');
        $this->expectExceptionCode(401);
        $this->render([], [], []);
    }
}
