<?php

namespace BO\Zmsbackend\Tests\Organisation\Api;

use BO\Zmsbackend\Helper\User;

class OrganisationByClusterTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "OrganisationByCluster";

    public function testRendering()
    {
        $this->setWorkstation()
            ->getUseraccount()
            ->setPermissions('cluster');
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
        $this->setWorkstation()
            ->getUseraccount()
            ->setPermissions('cluster');
        $this->expectException('\ErrorException');
        $this->render([], [], []);
    }

    public function testNotFound()
    {
        $this->setWorkstation()
            ->getUseraccount()
            ->setPermissions('cluster');        
        $this->expectException('\BO\Zmsbackend\Cluster\Exception\ClusterNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 999], [], []);
    }

    public function testOrganisationNotFound()
    {
        $this->setWorkstation()
            ->getUseraccount()
            ->setPermissions('cluster');        
        $this->expectException('\BO\Zmsbackend\Organisation\Exception\OrganisationNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 1], [], []);
    }
}
