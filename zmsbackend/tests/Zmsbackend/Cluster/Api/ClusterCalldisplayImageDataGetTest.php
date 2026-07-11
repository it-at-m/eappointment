<?php

namespace BO\Zmsbackend\Tests\Cluster\Api;

use BO\Zmsbackend\Helper\User;
use BO\Zmsentities\Useraccount;
use BO\Zmsentities\Workstation;
use BO\Zmsentities\Scope;

class ClusterCalldisplayImageDataGetTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "ClusterCalldisplayImageDataGet";

    const CLUSTER_ID = 109;

    public function testRendering()
    {
        $this->setWorkstation();
        $response = $this->render(['id' => self::CLUSTER_ID], [], []);
        $this->assertStringContainsString('mimepart.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testEmpty()
    {
        $this->setWorkstation();
        $this->expectException('\ErrorException');
        $this->render([], [], []);
    }

    public function testClusterNotFound()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('cluster');
        $this->expectException('\BO\Zmsbackend\Cluster\Exception\ClusterNotFound');

        $this->expectExceptionCode(404);
        $this->render(['id'=>999], [], []);
    }
}
