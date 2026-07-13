<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;

class ClusterDeleteTest extends Base
{
    protected $classname = "ClusterDelete";

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('cluster');
        $response = $this->render(['id' => 109], [], []);
        $this->assertStringContainsString('cluster.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testNotFound()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('cluster');
        $this->expectException('\BO\Zmsapi\Exception\Cluster\ClusterNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 999], [], []);
    }

    public function testMissingRights()
    {
        $this->setWorkstation();
        $this->expectException('\\BO\\Zmsentities\\Exception\\UserAccountMissingRights');
        $this->expectExceptionCode(403);
        $this->render(['id' => 109], [], []);
    }
}
