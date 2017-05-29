<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;

class WorkstationProcessWaitingnumberTest extends Base
{
    protected $classname = "WorkstationProcessWaitingnumber";

    const PROCESS_ID = 10030;

    const AUTHKEY = '1c56';

    public function testRendering()
    {
        $this->setWorkstation();
        User::$workstation->queue['clusterEnabled'] = 1;
        User::$workstation->scope['id'] = 141;
        $response = $this->render([], [
            '__body' => '{
                "id": '. self::PROCESS_ID .',
                "authKey": "'. self::AUTHKEY .'",
                "amendment": "Beispiel Termin"
            }'
        ], []);
        $this->assertContains('process.json', (string)$response->getBody());
        $this->assertContains('"status":"queued"', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testEmpty()
    {
        $this->setWorkstation();
        User::$workstation->queue['clusterEnabled'] = 1;
        User::$workstation->scope['id'] = 141;
        $this->setExpectedException('\BO\Mellon\Failure\Exception');
        $this->render([], [], []);
    }

    public function testScopeNotFound()
    {
        $this->setWorkstation();
        User::$workstation->queue['clusterEnabled'] = 1;
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
