<?php

namespace BO\Zmsapi\Tests;

class ClusterQueueTest extends Base
{
    protected $classname = "ClusterQueue";

    public function testRendering()
    {
        $response = $this->render(['id' => 109], [], []);
        $this->assertContains('queue.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testQueueEmpty()
    {
        $response = $this->render(['id' => 109], ['date' => '2015-04-01'], []);
        $this->assertContains('"data":{}', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testClusterNotFound()
    {
        $this->expectException('\BO\Zmsapi\Exception\Cluster\ClusterNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 999], [], []);
    }
}
