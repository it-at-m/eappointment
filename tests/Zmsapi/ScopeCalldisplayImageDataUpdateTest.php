<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;
use BO\Zmsentities\Useraccount;
use BO\Zmsentities\Workstation;
use BO\Zmsentities\Scope;

class ScopeCalldisplayImageDataUpdateTest extends Base
{
    protected $classname = "ScopeCalldisplayImageDataUpdate";

    const SCOPE_ID = 141;

    public function testRendering()
    {
        $department = (new \BO\Zmsentities\Department());
        $department->scopes[] = new \BO\Zmsentities\Scope(['id' => self::SCOPE_ID]);
        $this->setWorkstation()->getUserAccount()->setRights('scope')->addDepartment($department);
        $response = $this->render(['id' => self::SCOPE_ID], [
            '__body' => $this->readFixture("GetBase64Image.json")
        ], []);
        $this->assertContains('mimepart.json', (string)$response->getBody());
        $this->assertContains('"base64":true', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testNoRights()
    {
        $this->setWorkstation();
        $this->setExpectedException('BO\Zmsentities\Exception\UserAccountMissingRights');
        $this->render(['id' => self::SCOPE_ID], [
            '__body' => '',
        ], []);
    }

    public function testScopeNotFound()
    {
        $this->setWorkstation()->getUserAccount()->setRights('scope');
        $this->expectException('\BO\Zmsapi\Exception\Scope\ScopeNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 999], [], []);
    }
}
