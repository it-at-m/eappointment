<?php

namespace BO\Zmsbackend\Tests\Status\Api;

class StatusGetTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "StatusGet";

    public function testRendering()
    {
        $response = $this->render([], [
            '__header' => [
                'X-Token' => 'secure-token',
            ],
        ], []);
        $this->assertStringContainsString('status.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testStatusByWorkstationLogin()
    {
        $this->setWorkstation();
        $response = $this->render([], [], []);
        $this->assertStringContainsString('status.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testAuthentificationFailed()
    {
        $this->expectException('\BO\Zmsbackend\Status\Exception\StatusAuthentificationFailed');
        $this->expectExceptionCode(401);
        $this->render([], [], []);
    }
}
