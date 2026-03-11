<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;
use BO\Zmsentities\Useraccount;
use BO\Zmsentities\Workstation;
use BO\Zmsentities\Scope;

class ScopeEmergencyTest extends Base
{
    protected $classname = "ScopeEmergency";

    const SCOPE_ID = 143;

    public function testNoLogin()
    {
        $this->expectException('BO\Zmsentities\Exception\UserAccountMissingLogin');
        $this->expectExceptionCode(401);
        $this->render(['id' => self::SCOPE_ID], [
            '__body' => '',
        ], []);
    }

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->permissions['emergency'] = true;
        // emergency permission required by controller; ensure superuser path also works
        // (superuser is treated as having all permissions)
        // Legacy rights kept for backwards compatibility in tests.
        $response = $this->render(['id' => self::SCOPE_ID], [
            '__body' => '{
            }'
        ], []);
        $this->assertStringContainsString('scope.json', (string)$response->getBody());
        $this->assertStringContainsString('"activated":1', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testNoAccess()
    {
        $this->setWorkstation()->getUseraccount()->permissions['appointment'] = true;
        $this->expectException('\BO\Zmsapi\Exception\Scope\ScopeNoAccess');
        $this->expectExceptionCode(403);
        $this->render(['id' => 141], [
            '__body' => '',
        ], []);
    }
}
