<?php

namespace BO\Zmsapi\Tests;

class ScopeUpdateTest extends Base
{
    protected $classname = "ScopeUpdate";

    public function testRendering()
    {
        $department = (new \BO\Zmsentities\Department());
        $department->scopes[] = new \BO\Zmsentities\Scope(['id' => 141]);
        $this->setWorkstation()->getUserAccount()->setRights('scope')->addDepartment($department);
        $response = $this->render(['id' => 141], [
            '__body' => $this->readFixture('GetScope_lessData.json')
        ], []);
        $this->assertContains('scope.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testEmpty()
    {
        $this->setWorkstation()->getUseraccount()->setRights('scope');
        $this->setExpectedException('\BO\Mellon\Failure\Exception');
        $this->render([], [], []);
    }

    public function testUnvalidInput()
    {
        $this->setWorkstation()->getUseraccount()->setRights('scope');
        $this->expectException('\BO\Zmsentities\Exception\SchemaValidation');
        $this->expectExceptionCode(400);
        $this->render(['id' => 141], [
            '__body' => '{}'
        ], []);
    }

    public function testNotFound()
    {
        $this->setWorkstation()->getUseraccount()->setRights('scope');
        $this->expectException('\BO\Zmsapi\Exception\Scope\ScopeNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 999], [
            '__body' => $this->readFixture('GetScope_lessData.json')
        ], []);
    }

    public function testNoRights()
    {
        $this->setWorkstation();
        $this->setExpectedException('BO\Zmsentities\Exception\UserAccountMissingRights');
        $this->render(['id' => 141], [
            '__body' => $this->readFixture('GetScope_lessData.json')
        ], []);
    }
}
