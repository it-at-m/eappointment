<?php

namespace BO\Zmsapi\Tests;

class RequestListByClusterTest extends Base
{
    protected $classname = "RequestListByCluster";

    public function testRendering()
    {
        $response = $this->render(['id' => 110], [], []);
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
        $this->expectException('\BO\Zmsapi\Exception\Cluster\ClusterNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 9999], [], []);
    }
}
