<?php

namespace BO\Zmsbackend\Tests\Workstation\Api;

class WorkstationListByScopeTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "WorkstationListByScope";

    public function testRendering()
    {
        $this->setWorkstation();
        $response = $this->render(['id' => 141], [], []);
        $this->assertStringContainsString('"error":false', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testEmpty()
    {
        $this->setWorkstation();
        $this->expectException('\ErrorException');
        $this->render([], [], []);
    }

    public function testNotFound()
    {
        $this->setWorkstation();
        $this->expectException('\BO\Zmsbackend\Scope\Exception\ScopeNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 999], [], []);
    }
}
