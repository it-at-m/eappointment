<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;

class DepartmentUpdateTest extends Base
{
    protected $classname = "DepartmentUpdate";

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setRights('department')
            ->addDepartment([
                'id' => 999
            ]);
        $response = $this->render(["id"=> 999], [
            '__body' => '{
                  "id": 999,
                  "name": "Test Department Update"
              }'
        ], []);
        $this->assertStringContainsString('Test Department Update', (string)$response->getBody());
        $this->assertStringContainsString('department.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testEmpty()
    {
        $this->setWorkstation()->getUseraccount()->setRights('department');
        $this->expectException('\BO\Mellon\Failure\Exception');
        $this->render([], [], []);
    }

    public function testNotFound()
    {
        $this->setWorkstation()->getUseraccount()->setRights('department')
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
