<?php

namespace BO\Zmsbackend\Tests\Config\Api;

use \BO\Slim\Render;

class ConfigGetTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "ConfigGet";

    public function testRendering()
    {
        $response = $this->render([], [
            '__header' => array(
                'X-Token' => 'secure-token'
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
        $this->expectException('\BO\Zmsbackend\Config\Exception\ConfigAuthentificationFailed');
        $this->expectExceptionCode(401);
        $this->render([], [], []);
    }
}
