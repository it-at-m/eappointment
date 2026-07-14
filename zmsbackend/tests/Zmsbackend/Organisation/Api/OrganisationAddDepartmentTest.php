<?php

namespace BO\Zmsbackend\Tests\Organisation\Api;

class OrganisationAddDepartmentTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "OrganisationAddDepartment";

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('department')
            ->addDepartment([
                'id' => 74
            ]);
        $response = $this->render(['id' => 71], [
            '__body' => '{
                  "name": "Test Department"
              }'
        ], []);
        $this->assertStringContainsString('department.json', (string)$response->getBody());
        $this->assertStringContainsString('"name":"Test Department"', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testUnvalidDepartment()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('department')
            ->addDepartment([
                'id' => 74
            ]);
        $this->expectException('\\BO\\Mellon\\Failure\\Exception');
        $this->render(['id' => 71], [], []);
    }

    public function testMissingPermission()
    {
        $this->setWorkstation()->getUseraccount()
            ->addDepartment([
                'id' => 74
            ]);
        $this->expectException('BO\\Zmsentities\\Exception\\UserAccountMissingRights');
        $this->expectExceptionCode(403);
        $this->render(['id' => 71], [
            '__body' => '{
                  "name": "Test Department"
              }'
        ], []);
    }

    public function testNoEntityAccess()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('department');
        $this->expectException('BO\\Zmsentities\\Exception\\UserAccountMissingRights');
        $this->expectExceptionCode(403);
        $this->render(['id' => 71], [
            '__body' => '{
                  "name": "Test Department"
              }'
        ], []);
    }
}
