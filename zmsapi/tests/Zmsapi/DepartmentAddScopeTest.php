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
                      "source" : "dldb",
                      "displayName":"001"
                  }
              }'
        ], []);
        $this->assertStringContainsString('scope.json', (string)$response->getBody());
        $this->assertStringContainsString('"shortName":"Test Scope"', (string)$response->getBody());
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
        $this->expectException('BO\Zmsentities\Exception\UserAccountMissingRights');
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
