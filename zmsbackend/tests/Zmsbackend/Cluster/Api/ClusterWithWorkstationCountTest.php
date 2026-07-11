<?php

namespace BO\Zmsbackend\Tests\Cluster\Api;

use BO\Zmsbackend\Helper\User;

class ClusterWithWorkstationCountTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "ClusterWithWorkstationCount";

    public function testRendering()
    {
        $this->setWorkstation();
        $response = $this->render(['id' => 109], [], []);
        $this->assertStringContainsString('cluster.json', (string)$response->getBody());
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
        User::$workstation->useraccount->setPermissions('cluster');
        $this->expectException('\BO\Zmsbackend\Cluster\Exception\ClusterNotFound');

        $this->expectExceptionCode(404);
        $this->render(['id' => 999], [], []);
    }
}
