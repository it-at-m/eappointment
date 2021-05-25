<?php

namespace BO\Zmsapi\Tests;

class ProcessSearchTest extends Base
{
    protected $classname = "ProcessSearch";

    const SCOPE_ID = 141;

    public function testRendering()
    {
        $department = (new \BO\Zmsentities\Department());
        $department->scopes[] = new \BO\Zmsentities\Scope(['id' => self::SCOPE_ID]);
        $this->setWorkstation()->getUseraccount()->setRights('basic')->addDepartment($department);
        $response = $this->render([], ['query' => 'dayoff'], []);
        $this->assertStringContainsString('process.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testUnassignedScope()
    {
        $department = (new \BO\Zmsentities\Department());
        $department->scopes[] = new \BO\Zmsentities\Scope(['id' => 189]);
        $this->setWorkstation()->getUseraccount()->setRights('basic')->addDepartment($department);
        $response = $this->render([], ['query' => 'dayoff'], []);
        $this->assertStringNotContainsString('process.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testSuperuser()
    {
        $department = (new \BO\Zmsentities\Department());
        $department->scopes[] = new \BO\Zmsentities\Scope(['id' => 189]);
        $this->setWorkstation()->getUseraccount()->setRights('superuser')->addDepartment($department);
        $response = $this->render([], ['query' => 'dayoff'], []);
        $this->assertStringContainsString('process.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testWithLessData()
    {
        $department = (new \BO\Zmsentities\Department());
        $department->scopes[] = new \BO\Zmsentities\Scope(['id' => self::SCOPE_ID]);
        $this->setWorkstation()->getUseraccount()->setRights('basic')->addDepartment($department);
        $response = $this->render([], ['query' => 'dayoff', 'lessResolvedData' => 1], []);
        $this->assertStringContainsString('process.json', (string)$response->getBody());
        $this->assertStringNotContainsString('availability', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }
}
