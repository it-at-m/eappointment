<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;
use BO\Zmsentities\Useraccount;
use BO\Zmsentities\Workstation;
use BO\Zmsentities\Scope;

class WorkstationUpdateTest extends Base
{
    protected $classname = "WorkstationUpdate";

    const SCOPE_ID = 141;

    const PLACE = "12";

    const NEWPLACE = "13";

    const TESTUSER = "testuser";

    const LASTLOGIN = 1459504500; //1.4.2016 11:55

    public function testOveragedLogin()
    {
        $this->expectException('\BO\Zmsentities\Exception\UserAccountMissingLogin');
        $this->expectExceptionCode(401);

        User::$workstation = new Workstation([
            'id' => '137',
            'useraccount' => new Useraccount([
                'lastLogin' => 1447926465, //19.11.2015
                'id' => self::TESTUSER,
                'rights' => [
                    'basic' => true
                ]
            ]),
            'scope' => new Scope([
                'id' => self::SCOPE_ID,
            ])
        ]);
        $this->render([], ['__body' => json_encode(User::$workstation)], []);
    }

    public function testAssignedWorkstationExists()
    {
        $this->expectException('\BO\Zmsapi\Exception\Workstation\WorkstationAlreadyAssigned');
        $workstation = $this->setWorkstation();
        $workstation->getUseraccount()->lastLogin = self::LASTLOGIN;

        User::$assignedWorkstation = new Workstation([
            'id' => '137',
            'useraccount' => new Useraccount([
                'lastLogin' => self::LASTLOGIN,
                'id' => self::TESTUSER,
                'rights' => [
                    'basic' => true
                ]
            ]),
            'scope' => new Scope([
                'id' => self::SCOPE_ID,
            ])
        ]);

        $workstation = (new \BO\Zmsentities\Workstation())->getExample();
        $workstation->name = self::PLACE;
        $workstation->scope['id'] = self::SCOPE_ID;
        $workstation->useraccount['lastLogin'] = self::LASTLOGIN; //1.4.2016 11:55

        $response = $this->render([], [
            '__body' => json_encode($workstation)
        ], []);

        $this->assertContains('workstation.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testRendering()
    {
        User::$assignedWorkstation = null;
        $userworkstation = $this->setWorkstation();
        $userworkstation->getUseraccount()->lastLogin = self::LASTLOGIN;

        $workstation = (new \BO\Zmsentities\Workstation())->getExample();
        $workstation->name = self::NEWPLACE;
        $workstation->id = 138;
        $workstation->scope['id'] = self::SCOPE_ID;
        $workstation->useraccount['id'] = 'berlinonline';
        $workstation->useraccount['lastLogin'] = self::LASTLOGIN;
        $response = $this->render([], [
            '__body' => json_encode($workstation)
        ], []);
        $this->assertContains('workstation.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }
}
