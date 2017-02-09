<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;
use BO\Zmsentities\Useraccount;
use BO\Zmsentities\Workstation;
use BO\Zmsentities\Scope;

class DayoffUpdateTest extends Base
{
    protected $classname = "DayoffUpdate";

    const SCOPE_ID = 143;

    const YEAR = 2016;

    public function testNoLogin()
    {
        $this->setExpectedException('BO\Zmsentities\Exception\UserAccountMissingLogin');
        $this->render([self::YEAR], [
            '__body' => '',
        ], []);
    }

    public function testRendering()
    {
        User::$workstation = new Workstation([
            'id' => '138',
            'useraccount' => new Useraccount([
                'id' => 'berlinonline',
                'rights' => [
                    'superuser' => true
                ]
            ]),
            'scope' => new Scope([
                'id' => self::SCOPE_ID,
            ])
        ]);
        $dayoffList = new \BO\Zmsentities\Collection\DayOffList(
            json_decode($this->readFixture("GetDayoffList.json"))
        );
        $response = $this->render([self::YEAR], [
            '__body' => json_encode($dayoffList)
        ], []);
        $this->assertContains('dayoff.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }
}
