<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\DayOff;
use \BO\Zmsentities\DayOff as Entity;

class DayOffTest extends Base
{
    public function testBasic()
    {
        $dayOffList = (new DayOff())->readByYear('2016'); //all dayoff dates in 2016
        $this->assertTrue($dayOffList->hasEntityByDate('2016-12-25'), "XMas DayOff date 2016-12-25 not recognized.");
        //all dayoff dates of Department 77 Teichstr. 65 (Haus 1), 13407 Berlin.
        $dayOffList = (new DayOff())->readByDepartmentId('77');
        $this->assertEquals('1479250800', $dayOffList->getEntityByName('Personalversammlung')['date']);
    }

    public function testWriteCommonByYear()
    {
        $dayOffList = (new DayOff())->readCommonByYear(2016); //all dayoff with departmentid 0
        $dayOffList->addEntity($this->getTestEntity());
        $dayOffList = (new DayOff())->writeCommonDayoffsByYear($dayOffList, 2016);
        $this->assertEquals(1459461600, $dayOffList->getEntityByName('Test Feiertag')['date']);
    }

    protected function getTestEntity()
    {
        return new Entity(array(
          "date" => 1459461600,
          "name" => "Test Feiertag"
        ));
    }
}
