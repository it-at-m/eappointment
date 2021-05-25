<?php

namespace BO\Zmsapi\Tests;

class RequestListByScopeTest extends Base
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
        $this->expectException('\BO\Zmsapi\Exception\Scope\ScopeNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 9999], [], []);
    }
}
