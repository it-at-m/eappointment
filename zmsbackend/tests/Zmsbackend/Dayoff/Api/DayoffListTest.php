<?php

namespace BO\Zmsbackend\Tests\Dayoff\Api;

use BO\Zmsbackend\Helper\User;

class DayoffListTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "DayoffList";

    public function testRendering()
    {
        $this->setWorkstation();
        User::$workstation->useraccount->setRights('superuser');
        $response = $this->render(['year' => 2016], [], []);
        $this->assertStringContainsString('dayoff.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testEmpty()
    {
        $this->setWorkstation();
        User::$workstation->useraccount->setRights('superuser');
        $this->expectException('\ErrorException');
        $this->render([], [], []);
    }

    public function testNotFound()
    {
        $this->setWorkstation();
        User::$workstation->useraccount->setRights('superuser');
        $testYear = \App::$now->modify('+ 11years')->format('Y');
        $this->expectException('\BO\Zmsbackend\Dayoff\Exception\YearOutOfRange');
        $this->expectExceptionCode(404);
        $this->render(['year' => $testYear], [], []);
    }
}
