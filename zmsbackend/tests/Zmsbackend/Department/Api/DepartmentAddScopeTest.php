<?php

namespace BO\Zmsbackend\Tests\Department\Api;

class DepartmentAddScopeTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "DepartmentAddScope";

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('scope', 'department')
            ->addDepartment([
                'id' => 72
            ]);
        $response = $this->render(['id' => 72], [
            '__body' => '{
                  "shortName": "Test Scope",
                  "provider": {
                      "id": 122217,
                      "displayName": "B\u00fcrgeramt Heerstra\u00dfe",
                      "source" : "dldb"
                  }
              }'
        ], []);
        $this->assertStringContainsString('scope.json', (string)$response->getBody());
        $this->assertStringContainsString('"shortName":"Test Scope"', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testUnvalidScope()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('scope', 'department')
            ->addDepartment([
                'id' => 72
            ]);
        $this->expectException('\BO\Mellon\Failure\Exception');
        $this->render(['id' => 72], [], []);
    }

    public function testNoDepartmentPermission()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('scope');
        $this->expectException('BO\Zmsentities\Exception\UserAccountMissingRights');
        $this->expectExceptionCode(403);
        $this->render(['id' => 72], [
            '__body' => '{
                  "shortName": "Test Scope",
                  "provider": {
                      "id": 122217,
                      "displayName": "B\u00fcrgeramt Heerstra\u00dfe"
                  }
              }'
        ], []);
    }

    public function testNoScopePermission()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('department');
        $this->expectException('BO\Zmsentities\Exception\UserAccountMissingRights');
        $this->expectExceptionCode(403);
        $this->render(['id' => 72], [
            '__body' => '{
                "shortName": "Test Scope",
                "provider": {
                    "id": 122217,
                    "displayName": "B\u00fcrgeramt Heerstra\u00dfe"
                }
            }'
        ], []);
    }

    public function testNoDepartmentAndScopePermission()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('statistic');
        $this->expectException('BO\Zmsentities\Exception\UserAccountMissingRights');
        $this->expectExceptionCode(403);
        $this->render(['id' => 72], [
            '__body' => '{
                "shortName": "Test Scope",
                "provider": {
                    "id": 122217,
                    "displayName": "B\u00fcrgeramt Heerstra\u00dfe"
                }
            }'
        ], []);
    }
}
