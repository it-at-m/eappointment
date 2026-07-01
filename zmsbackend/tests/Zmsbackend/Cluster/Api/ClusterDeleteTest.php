<?php

namespace BO\Zmsbackend\Tests\Cluster\Api;

use BO\Zmsbackend\Helper\User;

class ClusterDeleteTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "ClusterDelete";

    public function testRendering()
    {
        $this->setWorkstation();
        User::$workstation->useraccount->setRights('cluster');
        $response = $this->render(['id' => 109], [], []);
        $this->assertStringContainsString('cluster.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testNotFound()
    {
        $this->setWorkstation();
        User::$workstation->useraccount->setRights('cluster');
        $this->expectException('\BO\Zmsbackend\Cluster\Exception\ClusterNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 999], [], []);
    }
}
