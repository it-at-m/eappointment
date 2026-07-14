<?php

namespace BO\Zmsbackend\Tests\Department\Api;

use BO\Zmsbackend\Helper\User;

class DepartmentUpdateTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "DepartmentUpdate";

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('department')
            ->addDepartment([
                'id' => 999
            ]);
        $response = $this->render(["id"=> 999], [
            '__body' => '{
                  "id": 999,
                  "name": "Test Department Update",
                  "email": "test@example.com"
              }'
        ], []);
        $this->assertStringContainsString('Test Department Update', (string)$response->getBody());
        $this->assertStringContainsString('department.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testEmpty()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('department');
        $this->expectException('\BO\Mellon\Failure\Exception');
        $this->render([], [], []);
    }

    public function testNotFound()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('department')
            ->addDepartment([
                'id' => 999
            ]);
        $this->expectException('BO\Zmsentities\Exception\UserAccountMissingDepartment');
        $this->expectExceptionCode(403);
        $this->render(["id"=> 1], [
            '__body' => '{
                  "id": 9999
              }'
        ], []);
    }

    public function testNoRights()
    {
        $this->setWorkstation()->getUseraccount()->addDepartment(['id' => 999]);
        $this->expectException('BO\Zmsentities\Exception\UserAccountMissingRights');
        $this->expectExceptionCode(403);
        $this->render(["id"=> 999], [
            '__body' => '{
                  "id": 999,
                  "name": "Test Department Update"
              }'
        ], []);
    }
}
