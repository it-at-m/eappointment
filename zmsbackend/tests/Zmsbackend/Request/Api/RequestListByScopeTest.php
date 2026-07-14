<?php

namespace BO\Zmsbackend\Tests\Request\Api;

class RequestListByScopeTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "RequestListByScope";

    public function testRendering()
    {
        $response = $this->render(['id' => 141], [], []);
        $this->assertStringContainsString('request.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testEmpty()
    {
        $this->expectException('\ErrorException');
        $this->render([], [], []);
    }

    public function testNotFound()
    {
        $this->expectException('\BO\Zmsbackend\Scope\Exception\ScopeNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 9999], [], []);
    }
}
