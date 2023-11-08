<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;

class ClusterUpdateTest extends Base
{
    protected $classname = "ClusterUpdate";

    public function testRendering()
    {
        $this->setWorkstation();
        User::$workstation->useraccount->setRights('cluster');
        $response = $this->render(["id"=> 109], [
            '__body' => '{
                  "id": 109,
                  "name": "Bürgeramt Heerstraße",
                  "hint": "",
                  "shortNameEnabled": true,
                  "callDisplayText": ""
              }'
        ], []);
        $this->assertStringContainsString('cluster.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testEmpty()
    {
        $this->setWorkstation();
        User::$workstation->useraccount->setRights('cluster');
        $this->expectException('\BO\Mellon\Failure\Exception');
        $this->render([], [], []);
    }

    public function testNotFound()
    {
        $this->setWorkstation();
        User::$workstation->useraccount->setRights('cluster');
        $this->expectException('\BO\Zmsapi\Exception\Cluster\ClusterNotFound');
        $this->expectExceptionCode(404);
        $this->render(["id"=> 999], [
            '__body' => '{
                  "id": 999
              }'
        ], []);
    }
}
