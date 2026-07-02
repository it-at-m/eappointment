<?php

namespace BO\Zmsapi\Tests;

class ScopeDeleteTest extends Base
{
    protected $classname = "ScopeDelete";

    public function testRendering()
    {
        $department = (new \BO\Zmsentities\Department());
        $department->scopes[] = new \BO\Zmsentities\Scope(['id' => 615]);
        $this->setWorkstation()->getUserAccount()->setPermissions('scope')->addDepartment($department);
        $response = $this->render(['id' => 615], [], []); //Ordnungsamt Charlottenburg
        $this->assertStringContainsString('Ordnungsamt Charlottenburg-Wilmersdorf ', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testEmpty()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('scope');
        $this->expectException('\ErrorException');
        $this->render([], [], []);
    }

    public function testScopeNotFound()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('scope');
        $this->expectException('\BO\Zmsapi\Exception\Scope\ScopeNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 999], [], []);
    }

    public function testNoRights()
    {
        $department = (new \BO\Zmsentities\Department());
        $department->scopes[] = new \BO\Zmsentities\Scope(['id' => 615]);
        $this->setWorkstation()->getUserAccount()->addDepartment($department);
        $this->expectException('BO\\Zmsentities\\Exception\\UserAccountMissingRights');
        $this->render(['id' => 615], [], []); //Ordnungsamt Charlottenburg
    }

    public function testNoEntityAccess()
    {
        $this->setWorkstation()->getUserAccount()->setPermissions('scope');
        $this->expectException('BO\\Zmsentities\\Exception\\UserAccountMissingRights');
        $this->render(['id' => 615], [], []);
    }
}
