<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;
use BO\Zmsentities\Useraccount;
use BO\Zmsentities\Workstation;
use BO\Zmsentities\Scope;

class ScopeEmergencyRespondTest extends Base
{
    protected $classname = "ScopeEmergencyRespond";

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
        $workstation = $this->setWorkstation();
        $workstation->name = '24';
        $response = $this->render(['id' => self::SCOPE_ID], [
            '__body' => '{
            }'
        ], []);
        $this->assertStringContainsString('scope.json', (string)$response->getBody());
        $this->assertStringContainsString('"acceptedByWorkstation":"24"', (string)$response->getBody());
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
