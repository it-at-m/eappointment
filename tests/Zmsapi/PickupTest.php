<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;

class PickupTest extends Base
{
    protected $classname = "Pickup";

    public function testRendering()
    {
        $this->setWorkstation();
        User::$workstation->queue['clusterEnabled'] = 1;
        User::$workstation->scope['id'] = 141;
        (new ProcessFinishedTest)->testRenderingPending();
        $response = $this->render([], [], []);
        $this->assertContains('process.json', (string)$response->getBody());
        $this->assertContains('"status":"pending"', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testScopeNotFound()
    {
        $this->setWorkstation();
        User::$workstation->scope['id'] = 999;
        $this->expectException('\BO\Zmsapi\Exception\Scope\ScopeNotFound');
        $this->expectExceptionCode(404);
        $this->render([], [], []);
    }

    public function testClusterNotFound()
    {
        $this->setWorkstation();
        User::$workstation->scope['id'] = 143; //no existing cluster for Bürgeramt Rathaus Mitte in Test
        User::$workstation->queue['clusterEnabled'] = 1;
        $this->expectException('\BO\Zmsapi\Exception\Cluster\ClusterNotFound');
        $this->expectExceptionCode(404);
        $this->render([], [], []);
    }

    public function testNoLogin()
    {
        $this->expectException('BO\Zmsentities\Exception\UserAccountMissingLogin');
        $this->expectExceptionCode(401);
        $this->render([], [], []);
    }
}
