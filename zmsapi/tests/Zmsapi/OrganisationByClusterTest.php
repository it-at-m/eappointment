<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;

class OrganisationByClusterTest extends Base
{
    protected $classname = "OrganisationByCluster";

    public function testRendering()
    {
        $this->setWorkstation();
        $response = $this->render(['id' => 109], [], []);
        $this->assertStringContainsString('organisation.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testReducedDataAccess()
    {
        $response = $this->render(['id' => 109], [], []);
        $this->assertStringContainsString('organisation.json', (string)$response->getBody());
        $this->assertStringContainsString('"reducedData":true', (string)$response->getBody());
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
        $this->expectException('\BO\Zmsapi\Exception\Cluster\ClusterNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 999], [], []);
    }

    public function testOrganisationNotFound()
    {
        $this->setWorkstation();
        $this->expectException('\BO\Zmsapi\Exception\Organisation\OrganisationNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 1], [], []);
    }
}
