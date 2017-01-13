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
        $this->setExpectedException('BO\Zmsentities\Exception\UserAccountMissingLogin');
        $this->render([self::SCOPE_ID], [
            '__body' => '',
        ], []);
    }

    public function testRendering()
    {
        User::$workstation = new Workstation([
            'id' => '123a',
            'useraccount' => new Useraccount([
                'id' => 'testuser',
            ]),
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
        User::$workstation = new Workstation([
            'id' => '123a',
            'useraccount' => new Useraccount([
                'id' => 'testuser',
            ]),
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
