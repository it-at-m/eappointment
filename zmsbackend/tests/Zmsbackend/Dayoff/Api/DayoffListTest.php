<?php

namespace BO\Zmsbackend\Tests\Dayoff\Api;

use BO\Zmsbackend\Helper\User;

class DayoffListTest extends \BO\Zmsbackend\Tests\Api\Base

{
    protected $classname = "DayoffList";

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('dayoff');
        $response = $this->render(['year' => 2016], [], []);
        $this->assertStringContainsString('dayoff.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testEmpty()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('dayoff');
        $this->expectException('\ErrorException');
        $this->render([], [], []);
    }

    public function testNotFound()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('dayoff');
        $testYear = \App::$now->modify('+ 11years')->format('Y');
        $this->expectException('\BO\Zmsbackend\Dayoff\Exception\YearOutOfRange');
        $this->expectExceptionCode(404);
        $this->render(['year' => $testYear], [], []);
    }
}
