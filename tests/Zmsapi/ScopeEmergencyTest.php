<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;
use BO\Zmsentities\Useraccount;
use BO\Zmsentities\Scope;

class ScopeEmergencyTest extends Base
{
    protected $classname = "ScopeEmergency";

    const SCOPE_ID = 143;

    public function testNoLogin()
    {
        $this->setExpectedException('BO\Zmsentities\Exception\UserAccountMissingLogin');
        $this->render([self::SCOPE_ID], [
            '__body' => '',
        ], []);
    }

    public function testRendering()
    {
        User::$current = new Useraccount([
            'id' => 'unittest',
            'scope' => new Scope([
                'id' => self::SCOPE_ID,
                ])
        ]);
        $this->render([self::SCOPE_ID], [
            '__body' => '{
            }'
        ], []);
    }

    public function testNoAccess()
    {
        User::$current = new Useraccount([
            'id' => 'unittest',
            'scope' => new Scope([
                'id' => self::SCOPE_ID + 1,
                ])
        ]);
        $this->setExpectedException('\BO\Zmsapi\Exception\Scope\ScopeNoAccess');
        $this->render([self::SCOPE_ID], [
            '__body' => '',
        ], []);
    }
}
