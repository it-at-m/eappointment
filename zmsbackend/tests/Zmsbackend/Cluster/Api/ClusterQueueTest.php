<?php

namespace BO\Zmsbackend\Tests\Cluster\Api;

use BO\Zmsbackend\Helper\User;

class ClusterQueueTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "ClusterQueue";

    public function testRendering()
    {
        $this->setWorkstation()
            ->getUseraccount()
            ->setPermissions('appointment');
        User::$workstation->useraccount->addDepartment(new \BO\Zmsentities\Department([
            'id' => 1,
            'scopes' => [
            ['id' => 141],
            ],
        ]));
        $response = $this->render(['id' => 109], [], []);
        $this->assertStringContainsString('queue.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testQueueEmpty()
    {
        $this->setWorkstation()
            ->getUseraccount()
            ->setPermissions('appointment');
        User::$workstation->useraccount->addDepartment(new \BO\Zmsentities\Department([
            'id' => 1,
            'scopes' => [
                ['id' => 141],
            ],
        ]));
        $response = $this->render(['id' => 109], ['date' => '2015-04-01'], []);
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testClusterNotFound()
    {
        $this->setWorkstation()
            ->getUseraccount()
            ->setPermissions('appointment');
        $this->expectException('\BO\Zmsbackend\Cluster\Exception\ClusterNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 999], [], []);
    }
}
