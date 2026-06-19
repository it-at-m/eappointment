<?php

namespace BO\Zmsbackend\Tests\Scope\Api;

class ScopeListByClusterTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "ScopeListByCluster";

    public function testRendering()
    {
        $this->setWorkstation();
        $response = $this->render(['id' => 109], [], []);
        $this->assertStringContainsString('scope.json', (string)$response->getBody());
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
        $this->expectException('\BO\Zmsbackend\Cluster\Exception\ClusterNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 999], [], []);
    }
}
