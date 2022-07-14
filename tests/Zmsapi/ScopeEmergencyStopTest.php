<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;
use BO\Zmsentities\Useraccount;
use BO\Zmsentities\Workstation;
use BO\Zmsentities\Scope;

class ScopeEmergencyStopTest extends Base
{
    protected $classname = "ScopeEmergencyStop";

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
        $this->setWorkstation();
        $response = $this->render(['id' => self::SCOPE_ID], [
            '__body' => '{
            }'
        ], []);
        $this->assertStringContainsString('scope.json', (string)$response->getBody());
        $this->assertStringContainsString('"activated":"0"', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testNoAccess()
    {
        $this->setWorkstation();
        $this->expectException('\BO\Zmsapi\Exception\Scope\ScopeNoAccess');
        $this->expectExceptionCode(403);
        $this->render(['id' => 141], [
            '__body' => '',
        ], []);
    }
}
