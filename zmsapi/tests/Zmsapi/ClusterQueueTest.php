<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;

class ClusterQueueTest extends Base
{
    protected $classname = "ClusterQueue";

    public function testRendering()
    {
        $this->setWorkstation();
        User::$workstation->useraccount->permissions['waitingqueue'] = true;
        $response = $this->render(['id' => 109], [], []);
        $this->assertStringContainsString('queue.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testQueueEmpty()
    {
        $this->setWorkstation();
        User::$workstation->useraccount->permissions['waitingqueue'] = true;
        $response = $this->render(['id' => 109], ['date' => '2015-04-01'], []);
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testClusterNotFound()
    {
        $this->setWorkstation();
        User::$workstation->useraccount->permissions['waitingqueue'] = true;
        $this->expectException('\BO\Zmsapi\Exception\Cluster\ClusterNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 999], [], []);
    }

    public function testMissingWaitingqueuePermissionThrows403()
    {
        $this->setWorkstation();
        $this->expectException('\BO\Zmsentities\Exception\UserAccountMissingPermissions');
        $this->render(['id' => 109], [], []);
    }
}
