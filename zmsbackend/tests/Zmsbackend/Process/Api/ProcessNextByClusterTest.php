<?php

namespace BO\Zmsbackend\Tests\Process\Api;

use BO\Zmsbackend\Helper\User;

class ProcessNextByClusterTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "ProcessNextByCluster";

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
        $this->assertStringContainsString('process.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testClusterWideCallDisabled()
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
        $response = $this->render(['id' => 109], ['allowClusterWideCall' => false], []);
        $this->assertStringContainsString('process.json', (string)$response->getBody());
        $this->assertStringContainsString('"id":0', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testEmpty()
    {
        $this->setWorkstation()
            ->getUseraccount()
            ->setPermissions('appointment');
        $this->expectException('\ErrorException');
        $this->render([], [], []);
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

    public function testEntityAccessRequiresAssignedDepartmentScopes()
    {
        $cluster = (new \BO\Zmsbackend\Cluster\Service\Cluster())->readEntity(109, 1);
        $scopeId = $cluster->scopes->getFirst()->id;

        $useraccount = $this->setWorkstation()
            ->getUseraccount()
            ->setPermissions('appointment');
        $useraccount->departments = [];

        $entityAccess = new \BO\Zmsentities\Useraccount\EntityAccess($cluster);
        $this->assertFalse(
            $useraccount->hasPermissions([$entityAccess]),
            'Without department scopes, cluster EntityAccess must fail'
        );

        $useraccount->addDepartment(new \BO\Zmsentities\Department([
            'id' => 1,
            'scopes' => [
                ['id' => $scopeId],
            ],
        ]));
        $this->assertTrue($useraccount->hasPermissions([$entityAccess]));
    }
}
