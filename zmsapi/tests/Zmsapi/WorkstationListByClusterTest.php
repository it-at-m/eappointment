<?php

namespace BO\Zmsapi\Tests;

class WorkstationListByClusterTest extends Base
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
        $this->expectException('\BO\Zmsapi\Exception\Cluster\ClusterNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 999], [], []);
    }
}
