<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;
use BO\Zmsentities\Useraccount;
use BO\Zmsentities\Workstation;
use BO\Zmsentities\Scope;

class ScopeGetTest extends Base
{
    protected $classname = "ScopeGet";

    const SCOPE_ID = 141;

    public function testReducedDataAccess()
    {
        $response = $this->render(['id' => self::SCOPE_ID], [], []); //Pankow
        $this->assertStringContainsString('scope.json', (string)$response->getBody());
        $this->assertStringContainsString('"reducedData":true', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testRendering()
    {
        $department = (new \BO\Zmsentities\Department());
        $department->scopes[] = new \BO\Zmsentities\Scope(['id' => self::SCOPE_ID]);
        $this->setWorkstation()->getUseraccount()->addDepartment($department);
        $response = $this->render(['id' => self::SCOPE_ID], [], []); //Pankow
        $this->assertStringContainsString('scope.json', (string)$response->getBody());
        $this->assertStringNotContainsString('"reducedData"', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testWithIsOpenedParamter()
    {
        $department = (new \BO\Zmsentities\Department());
        $department->scopes[] = new \BO\Zmsentities\Scope(['id' => self::SCOPE_ID]);
        $this->setWorkstation()->getUseraccount()->setPermissions('scope')->addDepartment($department);
        $response = $this->render(['id' => self::SCOPE_ID], ['getIsOpened' => 1, 'accessRights' => 'scope'], []); //Pankow
        $this->assertStringContainsString('isOpened', (string)$response->getBody());
        $this->assertStringContainsString('scope.json', (string)$response->getBody());
        $this->assertStringNotContainsString('"reducedData"', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testWithMissingAccessRight()
    {
        $this->expectException('\BO\Zmsentities\Exception\UserAccountMissingRights');
        $department = (new \BO\Zmsentities\Department());
        $department->scopes[] = new \BO\Zmsentities\Scope(['id' => self::SCOPE_ID]);
        $this->setWorkstation()->getUseraccount()->addDepartment($department);
        $this->render(['id' => self::SCOPE_ID], ['accessRights' => 'scope'], []);
    }

    public function testRestrictedscopePermission()
    {
        $department = (new \BO\Zmsentities\Department());
        $department->scopes[] = new \BO\Zmsentities\Scope(['id' => self::SCOPE_ID]);
        $this->setWorkstation()->getUseraccount()->setPermissions('restrictedscope')->addDepartment($department);
        $response = $this->render(['id' => self::SCOPE_ID], ['accessRights' => 'restrictedscope'], []);
        $this->assertStringContainsString('scope.json', (string)$response->getBody());
        $this->assertStringNotContainsString('"reducedData"', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testScopePermissionWithoutEntityAccess()
    {
        $this->expectException('\BO\Zmsentities\Exception\UserAccountMissingRights');
        // Login, Permission vorhanden, aber keine EntityAccess-Zuordnung für Scope 141
        $this->setWorkstation()->getUseraccount()->setPermissions('scope');
        $this->render(['id' => self::SCOPE_ID], ['accessRights' => 'scope'], []);
    }

    public function testEmpty()
    {
        $this->expectException('\ErrorException');
        $this->render([], [], []);
    }

    public function testScopeNotFound()
    {
        $this->expectException('\BO\Zmsapi\Exception\Scope\ScopeNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 999], [], []);
    }
}
