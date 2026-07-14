<?php

namespace BO\Zmsbackend\Tests\Scope\Api;

use BO\Zmsbackend\Helper\User;
use BO\Zmsentities\Useraccount;
use BO\Zmsentities\Workstation;
use BO\Zmsentities\Scope;

class ScopeWithWorkstationCountTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "ScopeWithWorkstationCount";

    const SCOPE_ID = 141;

    public function testRendering()
    {
        $department = (new \BO\Zmsentities\Department());
        $department->scopes[] = new \BO\Zmsentities\Scope(['id' => self::SCOPE_ID]);
        $this->setWorkstation()->getUserAccount()->setPermissions('appointment')->addDepartment($department);
        $response = $this->render(['id' => self::SCOPE_ID], [], []); //Pankow
        $this->assertStringContainsString('"workstationCount"', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testEmpty()
    {
        $this->expectException('\ErrorException');
        $this->render([], [], []);
    }

    public function testScopeNotFound()
    {
        $this->expectException('\BO\Zmsbackend\Scope\Exception\ScopeNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 999], [], []);
    }
}
