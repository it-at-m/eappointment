<?php

namespace BO\Zmsbackend\Tests\Cluster\Api;

use BO\Zmsbackend\Helper\User;

class ClusterGetTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "ClusterGet";

    public function testRendering()
    {
        $this->setWorkstation();
        User::$workstation->useraccount->addDepartment(new \BO\Zmsentities\Department([
            'id' => 1,
            'scopes' => [
                ['id' => 141],
            ],
        ]));

        $response = $this->render(['id' => 109], [], []);
        $this->assertStringContainsString('cluster.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testReducedDataAccess()
    {
        $response = $this->render(['id' => 109], [], []);
        $this->assertStringContainsString('cluster.json', (string)$response->getBody());
        $this->assertStringContainsString('"reducedData":true', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testWithScopeListStatusAvailability()
    {
        $this->setWorkstation();
        User::$workstation->useraccount->addDepartment(new \BO\Zmsentities\Department([
            'id' => 1,
            'scopes' => [
                ['id' => 141],
            ],
        ]));
        $response = $this->render(['id' => 109], ['getIsOpened' => 1], []);
        $this->assertStringContainsString('isOpened', (string)$response->getBody());
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
        $this->expectException('\BO\Zmsbackend\Cluster\Exception\ClusterNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 999], [], []);
    }
}
