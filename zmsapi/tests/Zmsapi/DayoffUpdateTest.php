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
        $this->expectException('BO\Zmsentities\Exception\UserAccountMissingLogin');
        $this->render(['year' => self::YEAR], [
            '__body' => '',
        ], []);
    }

    public function testRendering()
    {
        $this->setWorkstation();
        User::$workstation->useraccount->setRights('superuser');
        $dayoffList = new \BO\Zmsentities\Collection\DayoffList(
            json_decode($this->readFixture("GetDayoffList.json"))
        );
        $response = $this->render(['year' => self::YEAR], [
            '__body' => json_encode($dayoffList)
        ], []);
        $this->assertStringContainsString('dayoff.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testUnvalidInput()
    {
        $this->setWorkstation();
        User::$workstation->useraccount->setRights('superuser');
        $this->expectException('BO\Mellon\Failure\Exception');
        $this->render([], [], []);
    }

    public function testDatesInYear()
    {
        $this->setWorkstation();
        User::$workstation->useraccount->setRights('superuser');
        $this->expectException('\BO\Zmsentities\Exception\DayoffWrongYear');
        $this->expectExceptionCode(404);
        $dayoffList = new \BO\Zmsentities\Collection\DayoffList(
            json_decode($this->readFixture("GetDayoffList.json"))
        );
        $this->render(['year' => self::YEAR - 1], [
            '__body' => json_encode($dayoffList)
        ], []);
    }
}
