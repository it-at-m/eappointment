<?php

namespace BO\Zmsapi\Tests;

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
        $this->setWorkstation()->getUseraccount()->setPermissions('dayoff');
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
        $this->setWorkstation()->getUseraccount()->setPermissions('dayoff');
        $this->expectException('BO\Mellon\Failure\Exception');
        $this->render([], [], []);
    }

    public function testDatesInYear()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('dayoff');
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
