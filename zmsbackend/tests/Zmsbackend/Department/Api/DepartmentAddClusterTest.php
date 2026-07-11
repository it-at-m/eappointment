<?php

namespace BO\Zmsbackend\Tests\Department\Api;

class DepartmentAddClusterTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "DepartmentAddCluster";

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('department')
            ->addDepartment([
                'id' => 72
            ]);
        $response = $this->render(['id' => 72], [
            '__body' => '{
                "name": "Bürgeramt Test",
                "hint": "",
                "shortNameEnabled": true,
                "callDisplayText": ""
            }'
        ], []);
        $this->assertStringContainsString('cluster.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testUnvalidCluster()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('department')
            ->addDepartment([
                'id' => 72
            ]);
        $this->expectException('\BO\Mellon\Failure\Exception');
        $this->render(['id' => 72], [], []);
    }

    public function testNoEntityAccess()
    {
        // Has permission but no access to the specific department -> fails via EntityAccess/permission guard
        $this->setWorkstation()->getUseraccount()->setPermissions('department');
        $this->expectException('BO\\Zmsentities\\Exception\\UserAccountMissingRights');
        $this->expectExceptionCode(403);
        $this->render(['id' => 72], [
            '__body' => '{
                "name": "Bürgeramt Test",
                "hint": "",
                "shortNameEnabled": true,
                "callDisplayText": ""
            }'
        ], []);
    }

    public function testMissingPermission()
    {
        // Has department access but lacks the required department permission -> fails with missing rights
        $this->setWorkstation()->getUseraccount()->addDepartment(['id' => 72]);
        $this->expectException('BO\\Zmsentities\\Exception\\UserAccountMissingRights');
        $this->expectExceptionCode(403);
        $this->render(['id' => 72], [
            '__body' => '{
                "name": "Bürgeramt Test",
                "hint": "",
                "shortNameEnabled": true,
                "callDisplayText": ""
            }'
        ], []);
    }
}
