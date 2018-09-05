<?php

namespace BO\Zmsapi\Tests;

class DepartmentAddScopeTest extends Base
{
    protected $classname = "DepartmentAddScope";

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setRights('department')
            ->addDepartment([
                'id' => 72
            ]);
        $response = $this->render(['id' => 72], [
            '__body' => '{
                  "shortName": "Test Scope",
                  "provider": {
                      "id": 122217,
                      "source" : "dldb"
                  }
              }'
        ], []);
        $this->assertContains('scope.json', (string)$response->getBody());
        $this->assertContains('"shortName":"Test Scope"', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testUnvalidScope()
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
        $this->expectException('BO\Zmsentities\Exception\UserAccountMissingDepartment');
        $this->expectExceptionCode(403);
        $this->render(['id' => 72], [
            '__body' => '{
                  "shortName": "Test Scope",
                  "provider": {
                      "id": 122217
                  }
              }'
        ], []);
    }
}
