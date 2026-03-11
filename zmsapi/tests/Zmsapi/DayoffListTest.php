<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;

class DayoffListTest extends Base
{
    protected $classname = "DayoffList";

    public function testRendering()
    {
        $this->setWorkstation();
        User::$workstation->useraccount->permissions['dayoff'] = true;
        $response = $this->render(['year' => 2016], [], []);
        $this->assertStringContainsString('dayoff.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testEmpty()
    {
        $this->setWorkstation();
        User::$workstation->useraccount->permissions['dayoff'] = true;
        $this->expectException('\ErrorException');
        $this->render([], [], []);
    }

    public function testNotFound()
    {
        $this->setWorkstation();
        User::$workstation->useraccount->permissions['dayoff'] = true;
        $testYear = \App::$now->modify('+ 11years')->format('Y');
        $this->expectException('\BO\Zmsapi\Exception\Dayoff\YearOutOfRange');
        $this->expectExceptionCode(404);
        $this->render(['year' => $testYear], [], []);
    }

    public function testMissingDayoffPermissionThrows403()
    {
        $this->setWorkstation();
        $this->expectException('\BO\Zmsentities\Exception\UserAccountMissingPermissions');
        $this->render(['year' => 2016], [], []);
    }
}
