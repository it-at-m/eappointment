<?php

namespace BO\Zmsbackend\Tests\Workstation\Api;

class WorkstationListByClusterTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "WorkstationListByCluster";

    public function testRendering()
    {
        $this->setWorkstation();
        $response = $this->render(['id' => 110], [], []);
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
        $this->expectException('\BO\Zmsbackend\Cluster\Exception\ClusterNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 999], [], []);
    }
}
