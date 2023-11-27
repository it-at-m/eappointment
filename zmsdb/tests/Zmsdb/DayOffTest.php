<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\DayOff;
use \BO\Zmsentities\Dayoff as Entity;
use \BO\Zmsdb\Helper\CalculateDayOff as Helper;

class DayOffTest extends Base
{
    public function testBasic()
    {
        $dayOffList = (new Dayoff())->readByYear('2016'); //all dayoff dates in 2016
        $this->assertTrue($dayOffList->hasEntityByDate('2016-12-25'), "XMas Dayoff date 2016-12-25 not recognized.");
        //all dayoff dates of Department 77 Teichstr. 65 (Haus 1), 13407 Berlin.
        $dayOffList = (new Dayoff())->readByDepartmentId('77');
        $this->assertEquals('1479250800', $dayOffList->getEntityByName('Personalversammlung')['date']);
    }

    public function testWriteCommonByYear()
    {
        $dayOffList = (new Dayoff())->readCommonByYear(2016); //all dayoff with departmentid 0
        $dayOffList->addEntity($this->getTestEntity());
        $dayOffList = (new Dayoff())->writeCommonDayoffsByYear($dayOffList, 2016);
        $this->assertEquals(1459461600, $dayOffList->getEntityByName('Test Feiertag')['date']);
    }

    public function testWriteCommonUntilYear()
    {
        $dayOffList = (new Helper(2022, true))->writeDayOffListUntilYear();
        $this->assertEquals(1650146400, $dayOffList->getByDate('2022-04-17')->date);
    }

    public function testDeleteByTimeInterval()
    {
        (new Dayoff())->deleteByTimeInterval(3600);
        $dayOffList = (new Dayoff())->readByYear('2016'); //all dayoff dates in 2016
        $this->assertEquals(0, $dayOffList->count());
    }

    protected function getTestEntity()
    {
        return new Entity(array(
          "date" => 1459461600,
          "name" => "Test Feiertag"
        ));
    }
}
