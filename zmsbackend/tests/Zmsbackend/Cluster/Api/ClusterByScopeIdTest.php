<?php

namespace BO\Zmsbackend\Tests\Cluster\Api;

use BO\Zmsbackend\Helper\User;

class ClusterByScopeIdTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "ClusterByScopeId";

    public function testRendering()
    {
        $this->setWorkstation();
        $response = $this->render(['id' => 141], [], []);
        $this->assertStringContainsString('cluster.json', (string)$response->getBody());
        $this->assertStringContainsString('109', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testReducedDataWithoutLogin()
    {
        $response = $this->render(['id' => 141], [], []);
        $this->assertStringContainsString('cluster.json', (string)$response->getBody());
        $this->assertStringContainsString('reducedData', (string)$response->getBody());
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
        $response = $this->render(['id' => 999], [], []);
        $this->assertTrue(200 == $response->getStatusCode());
    }
}
