<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;

class ClusterByScopeIdTest extends Base
{
    protected $classname = "ClusterByScopeId";

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setRights('cluster');
        $response = $this->render(['id' => 141], [], []);
        $this->assertStringContainsString('cluster.json', (string)$response->getBody());
        $this->assertStringContainsString('109', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testWithoutRights()
    {
        $response = $this->render(['id' => 141], [], []);
        $this->assertStringContainsString('cluster.json', (string)$response->getBody());
        $this->assertStringContainsString('reducedData', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testEmpty()
    {
        $this->setWorkstation()->getUseraccount()->setRights('cluster');
        $this->expectException('\ErrorException');
        $this->render([], [], []);
    }

    public function testNotFound()
    {
        $this->setWorkstation()->getUseraccount()->setRights('cluster');
        $response = $this->render(['id' => 999], [], []);
        $this->assertTrue(200 == $response->getStatusCode());
    }
}
