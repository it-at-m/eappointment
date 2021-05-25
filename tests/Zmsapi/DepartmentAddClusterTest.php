<?php

namespace BO\Zmsapi\Tests;

class DepartmentAddClusterTest extends Base
{
    protected $classname = "DepartmentAddCluster";

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setRights('department')
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
        $this->setWorkstation()->getUseraccount()->setRights('department')
            ->addDepartment([
                'id' => 72
            ]);
        $this->expectException('\BO\Mellon\Failure\Exception');
        $this->render(['id' => 72], [], []);
    }

    public function testNoRights()
    {
        $this->setWorkstation()->getUseraccount()->setRights('department');
        $this->expectException('BO\Zmsentities\Exception\UserAccountMissingRights');
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
