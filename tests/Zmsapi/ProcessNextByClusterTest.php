<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;

class ProcessNextByClusterTest extends Base
{
    protected $classname = "ProcessNextByCluster";

    public function testRendering()
    {
        $this->setWorkstation();
        $response = $this->render(['id' => 109], [], []);
        $this->assertContains('process.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testEmpty()
    {
        $this->setWorkstation();
        $this->setExpectedException('\ErrorException');
        $this->render([], [], []);
    }

    public function testClusterNotFound()
    {
        $this->setWorkstation();
        $this->expectException('\BO\Zmsapi\Exception\Cluster\ClusterNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 999], [], []);
    }
}
