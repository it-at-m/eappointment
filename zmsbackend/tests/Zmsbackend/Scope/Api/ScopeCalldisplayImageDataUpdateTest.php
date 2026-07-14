<?php

namespace BO\Zmsbackend\Tests\Scope\Api;

use BO\Zmsbackend\Helper\User;
use BO\Zmsentities\Useraccount;
use BO\Zmsentities\Workstation;
use BO\Zmsentities\Scope;

class ScopeCalldisplayImageDataUpdateTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "ScopeCalldisplayImageDataUpdate";

    const SCOPE_ID = 141;

    public function testRendering()
    {
        $department = (new \BO\Zmsentities\Department());
        $department->scopes[] = new \BO\Zmsentities\Scope(['id' => self::SCOPE_ID]);
        $this->setWorkstation()->getUserAccount()->setPermissions('calldisplay')->addDepartment($department);
        $response = $this->render(['id' => self::SCOPE_ID], [
            '__body' => $this->readFixture("GetBase64Image.json")
        ], []);
        $this->assertStringContainsString('mimepart.json', (string)$response->getBody());
        $this->assertStringContainsString('"base64":true', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testNoRights()
    {
        $department = (new \BO\Zmsentities\Department());
        $department->scopes[] = new \BO\Zmsentities\Scope(['id' => self::SCOPE_ID]);
        $this->setWorkstation()->getUserAccount()->addDepartment($department);
        $this->expectException('BO\Zmsentities\Exception\UserAccountMissingRights');
        $this->render(['id' => self::SCOPE_ID], [
            '__body' => $this->readFixture("GetBase64Image.json"),
        ], []);
    }

    public function testScopeNotFound()
    {
        $this->setWorkstation()->getUserAccount()->setPermissions('scope');
        $this->expectException('\BO\Zmsbackend\Scope\Exception\ScopeNotFound');

        $this->expectExceptionCode(404);
        $this->render(['id' => 999], [], []);
    }
}
