<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;
use BO\Zmsentities\Useraccount;
use BO\Zmsentities\Workstation;
use BO\Zmsentities\Scope;

class ClusterCalldisplayImageDataUpdateTest extends Base
{
    protected $classname = "ClusterCalldisplayImageDataUpdate";

    const CLUSTER_ID = 109;

    public function testRendering()
    {
        $this->setWorkstation()->getUserAccount()->setRights('cluster');
        $response = $this->render(['id' => self::CLUSTER_ID], [
            '__body' => $this->readFixture("GetBase64Image.json")
        ], []);
        $this->assertContains('mimepart.json', (string)$response->getBody());
        $this->assertContains('"base64":true', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testNoRights()
    {
        $this->setWorkstation();
        $this->setExpectedException('BO\Zmsentities\Exception\UserAccountMissingRights');
        $this->render(['id' => self::CLUSTER_ID], [
            '__body' => '',
        ], []);
    }

    public function testClusterNotFound()
    {
        $this->setWorkstation()->getUserAccount()->setRights('cluster');
        $this->expectException('\BO\Zmsapi\Exception\Cluster\ClusterNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 999], [], []);
    }
}
